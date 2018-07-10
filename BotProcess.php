<?php

require 'vendor/autoload.php';

use InstagramAPI\Instagram;

use Bot\AccountsBot;
use Bot\GeotagBot;
use Bot\HashtagBot;
use Repository\AccountsRepository;
use Repository\UsersRepository;
use Repository\StatisticsRepository;

use Util\Logger;

const MAX_POINTS_COUNT = 200;
const BREAK_TIME = 3600; //SECONDS
const DAY = 86400;
$maxDailyPointsCount = 0;


Logger::logToConsole("Process started with ID " . $argv[1]);
$id = $argv[1];

Logger::setFilePath("botProcess$id");

$botProcessStatistics = new \Entity\BotProcessStatistics($id);
$account = AccountsRepository::getBy(['id' => $id])[0];

try {
    $user = UsersRepository::getBy(['id' => $id])[0];
    $instagram = new Instagram(false, false);
    $instagram->login($user->getLogin(), $user->getPassword());

    $bots = [];

    $settings = $user->getSettings();
    if ($settings['genesis_account_bot'])
        array_push($bots, new AccountsBot($instagram, $settings));
    if ($settings['hashtag_bot'])
        array_push($bots, new HashtagBot($instagram, $settings));
    if ($settings['geotag_bot'])
        array_push($bots, new GeotagBot($instagram, $settings));

    if ($settings['likes'])
        $maxDailyPointsCount += 1000;
    if ($settings['followings'])
        $maxDailyPointsCount += 1000;
    if ($settings['comments'])
        $maxDailyPointsCount += 500;


    if (count($bots) > 0) {
        if ($account->getDailyPointsCount() == 0)
            $account->setLimitTime(time() + DAY);

        while (true) {
            foreach ($bots as $bot) {
                $bot->run();

                $botProcessStatistics->addPoints($bot->getBotProcessStatistics());
                $bot->resetBotProcessStatistics();

                Logger::logToConsole("Points: " . $botProcessStatistics->getPointsCount() . " ID: " . $id);

                if ($botProcessStatistics->getPointsCount() >= MAX_POINTS_COUNT)
                    break 2;
            }
        }
    }
    Logger::logToConsole("Bot process with ID $id finished");
} catch (\Exception $e) {
    Logger::log("Bot process crush: " . $e->getMessage() . "\n" . $e->getTraceAsString());
} finally {
    StatisticsRepository::addPoints($botProcessStatistics);
    $account->setDailyPointsCount(
        $account->getDailyPointsCount() + $botProcessStatistics->getPointsCount()
    );

    if ($account->getDailyPointsCount() >= $maxDailyPointsCount) {
        $account->setDailyPointsCount(0);
        $account->setTime($account->getLimitTime());
    } else {
        $account->setDailyPointsCount(
            $account->getDailyPointsCount() + $botProcessStatistics->getPointsCount()
        );
        $account->setTime(time() + BREAK_TIME);
    }

    $account->setInProcess(false);
    AccountsRepository::update($account);
    Logger::logToConsole("Finally-block has reached with ID $id");
}




//\InstagramScraper\Instagram::setProxy([
//    'address' => $argv['2'],
//    'port'    => $argv['3'],
//    'tunnel'  => true,
//    'timeout' => 30,
//]);