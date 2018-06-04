<?php

require 'vendor/autoload.php';
require_once 'src/AccountsBot.php';
require_once 'src/HashtagsBot.php';

function getUserAndPass(){
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

try {
    $arr = getUserAndPass();

    $instagram = InstagramScraper\Instagram::withCredentials($arr[0], $arr[1]);
    $instagram->login();

    $settings = [
        'comments_enabled' => true,
        'likes_enabled' => true,
        'following_enabled' => true,
    ];

    /*

    $bot = new AccountsBot($instagram, $settings);
    $bot->start($account);
*/

    $hashtags = ['follow4like', "follow4likes", "follow",
            "follow4", "follow4folow", "followers",
            "following", "liker", "likers",
            "likelike", "liked", "likeme", "like4follow"];
    $bot1 = new HashtagsBot($instagram, $settings, $hashtags);
    $bot1->start();

} catch(Exception $exception){
    echo "\nException\n".$exception;
}
