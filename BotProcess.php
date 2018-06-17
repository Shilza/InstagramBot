<?php

require 'vendor/autoload.php';

use InstagramScraper\Instagram;

use Bot\AccountsBot;
use Bot\GeotagBot;
use Bot\HashtagBot;
use Entity\Account;
use Repository\AccountsRepository;
use Repository\UsersRepository;

const MAX_POINTS_COUNT = 70;

echo "ID: $argv[1]";
$id = $argv[1];
try {
//$id = 2436801585;
    $user = UsersRepository::getBy(['id' => $id])[0];
    $instagram = Instagram::withCredentials($user->getLogin(), $user->getPassword());
    $instagram->login();

//\InstagramScraper\Instagram::setProxy([
//    'address' => $argv['2'],
//    'port'    => $argv['3'],
//    'tunnel'  => true,
//    'timeout' => 30,
//]);

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

    $pointsCount = 0;
    while (true) {
        foreach ($bots as $bot) {
            $bot->run();
            $pointsCount += $bot->getPointsCount();
            if ($pointsCount >= MAX_POINTS_COUNT)
                break 2;
        }
    }
} finally {
    AccountsRepository::update(new Account($id, time() + 120, false));
}
