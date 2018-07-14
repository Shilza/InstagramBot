<?php

require 'vendor/autoload.php';

use Entity\BotProcessStatistics;
use InstagramAPI\Instagram;

use Bot\AccountsBot;
use Bot\GeotagBot;
use Bot\HashtagBot;
use Repository\AccountsRepository;
use Repository\UsersRepository;
use Repository\StatisticsRepository;

use Util\Logger;

$maxPointsCount = 0;
const BREAK_TIME = 3600; //1 HOUR
const DAY = 86400;
$maxDailyPointsCount = 0;

$id = $argv[1];

Logger::setFilePath($id);

Logger::info("BotProcess started");

$botProcessStatistics = new BotProcessStatistics($id);
$account = AccountsRepository::getBy(['id' => $id])[0];

try {
    $user = UsersRepository::getBy(['id' => $id])[0];
    $instagram = new Instagram(false, false);
    $instagram->login($user->getLogin(), $user->getPassword());
    Logger::info("login");


//    if() {
//        $dialogs = getDirectDialogs();
//        sendMessagesToNewFolllowers($instagram->people->getRecentActivityInbox()->getNewStories());
//        sendMessagesToNewFolllowers($instagram->people->getRecentActivityInbox()->getOldStories());
//    }

    $bots = [];

    $settings = $user->getSettings();
    if ($settings['genesis_account_bot'])
        array_push($bots, new AccountsBot($instagram, $settings));
    if ($settings['hashtag_bot'])
        array_push($bots, new HashtagBot($instagram, $settings));
    if ($settings['geotag_bot'])
        array_push($bots, new GeotagBot($instagram, $settings));

    if ($settings['likes']) {
        $maxDailyPointsCount += 1000;
        $maxPointsCount += 75;
    }
    if ($settings['followings']) {
        $maxDailyPointsCount += 1000;
        $maxPointsCount += 75;
    }
    if ($settings['comments']) {
        $maxDailyPointsCount += 500;
        $maxPointsCount += 50;
    }

    if (count($bots) > 0) {
        if ($account->getDailyPointsCount() == 0)
            $account->setLimitTime(time() + DAY);
        else if($account->getLimitTime() < time()){
            $account->setDailyPointsCount(0);
            $account->setLimitTime(time() + DAY);
        }

        while (true)
            foreach ($bots as $bot) {
                $bot->run();

                $botProcessStatistics->addPoints($bot->getBotProcessStatistics());
                $bot->resetBotProcessStatistics();

                Logger::trace("Points: " . $botProcessStatistics->getPointsCount());

                if ($botProcessStatistics->getPointsCount() >= $maxPointsCount)
                    break 2;
            }
    }
    Logger::info("Bot process with finished");
}
catch (\Exception $e) {
    Logger::error("Bot process crush: " . $e->getMessage() . "\n" . $e->getTraceAsString());
} finally {
    StatisticsRepository::addPoints($botProcessStatistics);
    $account->setDailyPointsCount(
        $account->getDailyPointsCount() + $botProcessStatistics->getPointsCount()
    );

    if ($account->getDailyPointsCount() >= $maxDailyPointsCount) {
        $account->setDailyPointsCount(0);
        $account->setTime($account->getLimitTime());
    } else
        $account->setTime(time() + BREAK_TIME);

    $account->setInProcess(false);
    AccountsRepository::update($account);
    Logger::info("Finally-block has reached with");
}


function getDirectDialogs(){
    global $instagram;

    $cursor = null;
    $threads = [];
    do {
        $inbox = $instagram->direct->getInbox($cursor)->getInbox();
        foreach ($inbox->getThreads() as $thread)
            if(count($thread->getUsers()) === 1)
                array_push($threads, $thread->getUsers()[0]->getPk());

        $cursor = $inbox->getOldestCursor();
    } while (isset($cursor));


    return $threads;
}

function sendMessagesToNewFolllowers(array $stories){
    global $instagram;
    global $dialogs;

    foreach ($stories as $story)
        if (stristr($story->getArgs()->getText(), "started following") !== false) {
            $followsId = $story->getArgs()->getProfileId();
            if(!in_array($followsId, $dialogs)){
                $instagram->direct->sendText(['users' => [$followsId]], "Hii");
                sleep(mt_rand(8, 13));
            }
        }
}