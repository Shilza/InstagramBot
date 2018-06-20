<?php

require 'vendor/autoload.php';

use Entity\User;
use InstagramScraper\Instagram;
use Repository\CommentsRepository;
use Repository\FollowsRepository;
use Repository\UsersRepository;
use Util\AccountWorker;
use Repository\StatisticsRepository;

function getUserAndPass()
{
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

function registration($login, $pass, &$instagram, &$settings){

    $instagram = Instagram::withCredentials($login, $pass);
    $instagram->login();

    $settings = [
        'comments_selected' => true,
        'likes_selected' => true,
        'following_selected' => true,
        'genesis_account_bot_selected' => true,
        'hashtag_bot_selected' => true,
        'geotag_bot_selected' => true
    ];

    $user = new User($instagram->getAccount($login)->getId(), $login, $pass,
        null, time(), 0, $settings);

    //UsersRepository::add($user);
    //CommentsRepository::createTable($user->getUserId());
    //FollowsRepository::createTable($user->getUserId());
    //StatisticsRepository::add(new \Entity\BotProcessStatistics($user->getUserId()));

    return $user;
}



$arr = getUserAndPass();

$user = registration($arr[0], $arr[1], $instagram, $settings);

/*
$fl = $instagram->getFollowing($instagram->getAccount($instagram->getSessionUsername())->getId(), 100);
foreach ($fl as $item) {
    try {
        $instagram->unfollow($item['id']);
    }
    catch (Exception $e){
        if (substr($e->getMessage(), 17, 3) == 403)
            sleep(3);
    }
}
*/
