<?php

require 'vendor/autoload.php';
require_once 'src/Bots/AccountsBot.php';
require_once 'src/Bots/HashtagBot.php';
require_once 'src/Bots/GeotagBot.php';
require_once 'src/AccountWorker.php';
require_once 'src/Entities/User.php';
require_once 'src/Repositories/UsersRepository.php';
require_once 'src/Entities/Comment.php';
require_once 'src/Repositories/CommentsRepository.php';
require_once 'src/Entities/FollowedUser.php';
require_once 'src/Repositories/FollowsRepository.php';
require_once 'src/Repositories/AccountsRepository.php';


const MAX_POINTS_COUNT = 70;

echo "ID: $argv[1]";
$id = $argv[1];
sleep(5);
//$id = 2436801585;
$user = UsersRepository::getBy(['id' => $id])[0];
$instagram = InstagramScraper\Instagram::withCredentials($user->getLogin(), $user->getPassword());
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

if($user->getSettings()['genesis_account_bot_selected'])
    array_push($bots, new AccountsBot($instagram, $user->getSettings()));
if($user->getSettings()['hashtag_bot_selected'])
    array_push($bots, new HashtagBot($instagram, $user->getSettings()));
if($user->getSettings()['geotag_bot_selected'])
    array_push($bots, new GeotagBot($instagram, $user->getSettings(), $geotags));

$pointsCount = 0;
while(true){
    foreach ($bots as $bot){
        $bot->start();
        $pointsCount += $bot->getPointsCount();
        if($pointsCount >= MAX_POINTS_COUNT)
            break 2;
    }
}

AccountsRepository::update(new Account($id, time()+120, false));
