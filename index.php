<?php

require 'vendor/autoload.php';

use Entity\User;

function getUserAndPass()
{
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

function registration($login, $pass, &$instagram, &$settings)
{

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


$instagram = new \InstagramAPI\Instagram(false, false);
$instagram->login("macmilan_price", "192.168.39.26a");

try{
    $a = \InstagramAPI\InstagramID::fromCode('BlDQbZ_BYgs');
    $instagram->media->comment($a, "hello");
} catch (Exception $e){
    var_dump($e);
}
