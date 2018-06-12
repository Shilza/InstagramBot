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


const MAX_POINTS_COUNT = 200;

//$id = $argv[1];
$id = 2436801585;
AccountsRepository::update(new Account($id, time()+15, false));

$user = UsersRepository::getBy(['id' => $id])[0];

$instagram = InstagramScraper\Instagram::withCredentials($user->getLogin(), $user->getPassword());
$instagram->login();

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

$sas = 0;
while(true){
    foreach ($bots as $bot){
        echo "NEW BOT STARTED ".gettype($bot)." Sas: $sas \n";
        $bot->start();
        echo "Aaa".$bot->getPointsCount() ."\n";
        $sas += $bot->getPointsCount();
        if($sas >= MAX_POINTS_COUNT)
            break 2;
    }
}