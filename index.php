<?php

require 'vendor/autoload.php';

use Entity\User;
use InstagramScraper\Instagram;
use Repository\CommentsRepository;
use Repository\FollowsRepository;
use Repository\UsersRepository;
use Util\AccountWorker;

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

    UsersRepository::add($user);
    CommentsRepository::createTable($user->getUserId());
    FollowsRepository::createTable($user->getUserId());

    return $user;
}

//7955715631
//$arr = getUserAndPass();
//
$user = registration('macmilan_price', '192.168.39.26a', $instagram, $settings);
//$accountWorker = new AccountWorker($instagram);

//$accountWorker->unfollowFromAll();

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
