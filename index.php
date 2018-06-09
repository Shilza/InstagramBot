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
    $arr = getUserAndPass();

    $instagram = InstagramScraper\Instagram::withCredentials($arr[0], $arr[1]);
    $instagram->login();

    var_dump(UsersRepository::getBy(['id' => 12]));

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

    /*
    $settings = [
        'comments_enabled' => true,
        'likes_enabled' => true,
        'following_enabled' => true,
    ];

    $user = new User("23df4", "sdf", "sdfsdf", null, 12341234, 0, $settings);
    $cm = new Comment(23424, 234234, 234234, "dfgdfg");
    $fl = new FollowedUser(234234, 234234);
*/

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