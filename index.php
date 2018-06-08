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

try {

    $settings = [
        'comments_enabled' => true,
        'likes_enabled' => true,
        'following_enabled' => true,
    ];


} catch (Exception $exception) {
    echo "\nException\n" . $exception;
}




//$aw = new AccountWorker($instagram);


/*    $arr = getUserAndPass();

    $instagram = InstagramScraper\Instagram::withCredentials($arr[0], $arr[1]);
    $instagram->login();
 *  $settings = [
    'comments_enabled' => true,
    'likes_enabled' => true,
    'following_enabled' => true,
];

$bot = new AccountsBot($instagram, $settings);
$bot->start($account);

$hashtags = ['follow4like', "follow4likes", "follow",
    "follow4", "follow4folow", "followers",
    "following", "liker", "likers",
    "likelike", "liked", "likeme", "like4follow", "instalike", "likeit"];
$bot1 = new HashtagBot($instagram, $settings, $hashtags);
$bot1->start();

$geotags = [
    'Milan', "India", "Berlin"
];
$bot2 = new GeotagBot($instagram, $settings, $geotags);
$bot2->start();
*/
//$aw = new AccountWorker($instagram);

/*
 *
 * */