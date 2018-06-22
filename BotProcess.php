<?php

require 'vendor/autoload.php';

use InstagramScraper\Instagram;

use Bot\AccountsBot;
use Bot\GeotagBot;
use Bot\HashtagBot;
use Entity\Account;
use Repository\AccountsRepository;
use Repository\UsersRepository;
use Repository\StatisticsRepository;

use Util\Logger;

const MAX_POINTS_COUNT = 500;
const PAUSE = 600; //SECONDS

echo "ID: $argv[1] ";
$id = $argv[1];

Logger::setFilePath("botProcess$id");

$botProcessStatistics = new \Entity\BotProcessStatistics($id);

try {
    $user = UsersRepository::getBy(['id' => $id])[0];
    $instagram = Instagram::withCredentials($user->getLogin(), $user->getPassword());
    $instagram->login();

    $geotags = [
        'California', "India", "Kiev"
    ];

    $bots = [];

    if ($user->getSettings()['genesis_account_bot_selected'])
        array_push($bots, new AccountsBot($instagram, $user->getSettings()));
    if ($user->getSettings()['hashtag_bot_selected'])
        array_push($bots, new HashtagBot($instagram, $user->getSettings()));
    if ($user->getSettings()['geotag_bot_selected'])
        array_push($bots, new GeotagBot($instagram, $user->getSettings(), $geotags));

    while (true) {
        foreach ($bots as $bot) {
            $bot->run();

            $botProcessStatistics->addPoints($bot->getBotProcessStatistics());
            $bot->resetBotProcessStatistics();

            if ($botProcessStatistics->getPointsCount() >= MAX_POINTS_COUNT)
                break 2;
        }
    }
} catch (\Exception $e){
    Logger::log("Bot process crush: ".$e->getMessage()."\n".$e->getTraceAsString());
} finally {
    StatisticsRepository::addPoints($botProcessStatistics);
    AccountsRepository::update(new Account($id, time() + PAUSE, false));
}




//\InstagramScraper\Instagram::setProxy([
//    'address' => $argv['2'],
//    'port'    => $argv['3'],
//    'tunnel'  => true,
//    'timeout' => 30,
//]);