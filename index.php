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

function getUserAndPass()
{
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

function registration($login, $pass, &$instagram, &$settings){

    $instagram = InstagramScraper\Instagram::withCredentials($login, $pass);
    $instagram->login();

    $settings = [
        'comments_enabled' => true,
        'likes_enabled' => true,
        'following_enabled' => true,
    ];

    $user = new User($instagram->getAccount($login)->getId(), $login, $pass, null, 1234234, 0, $settings);

    UsersRepository::add($user);
    CommentsRepository::createTable($user->getUserId());
    FollowsRepository::createTable($user->getUserId());

    return $user;
}


$arr = getUserAndPass();

$user = registration($arr[0], $arr[1], $instagram, $settings);

$geotags = [
    'California', "India", "Kiev"
];
$bot2 = new GeotagBot($instagram, $user->getSettings(), $geotags);
$bot2->start();



/*
 *


$bot = new AccountsBot($instagram, $settings);
$bot->start();


$hashtags = ['follow4like', "follow4likes", "follow",
    "follow4", "follow4folow", "followers",
    "following", "liker", "likers",
    "likelike", "liked", "likeme", "like4follow", "instalike", "likeit"];
$bot1 = new HashtagBot($instagram, $user->getSettings(), $hashtags);
$bot1->start();



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
