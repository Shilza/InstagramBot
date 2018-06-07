<?php

require 'vendor/autoload.php';
require_once 'src/Bots/AccountsBot.php';
require_once 'src/Bots/HashtagBot.php';
require_once 'src/Bots/GeotagBot.php';
require_once 'src/AccountWorker.php';

function getUserAndPass()
{
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

try {
    $arr = getUserAndPass();

    $instagram = InstagramScraper\Instagram::withCredentials($arr[0], $arr[1]);
    $instagram->login();

    //$aw = new AccountWorker($instagram);


    /*
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

} catch (Exception $exception) {
    echo "\nException\n" . $exception;
}


/*
 *
 *     $commentsTableName = 'comments'.$instagram->getAccount($instagram->getSessionUsername())->getId();

    $link = new PDO('mysql:host=localhost;dbname=InstaTest', "root", "");
    $query = $link->prepare("CREATE TABLE ".$commentsTableName.
        "(
          id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
          media_id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
          text VARCHAR(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
          date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
        ) ENGINE = InnoDB");

    $query->execute();
 *
 *
 *
 *     $login = "dayofdeath";
    $id = $instagram->getAccount($login)->getId();
    $password = "192.168.39.26";
    $last_activity = time();
    $money = 0;
    $following_enabled = true;
    $likes_enabled = true;
    $comments_enabled = true;

 *     $query = $link->prepare(
        "INSERT INTO users (id, login, password, last_activity, money, following_enabled, likes_enabled, comments_enabled)
                  VALUES (:id, :login, :password, :last_activity, :money, :following_enabled, :likes_enabled, :comments_enabled)");
    $query->bindParam(':id',$id);
    $query->bindParam(':login',$login);
    $query->bindParam(':password', $password);
    $query->bindParam(':last_activity', $last_activity);
    $query->bindParam(':money', $money);
    $query->bindParam(':following_enabled', $following_enabled);
    $query->bindParam(':likes_enabled', $likes_enabled);
    $query->bindParam(':comments_enabled', $comments_enabled);

    $query->execute();





CREATE TABLE `InstaTest`.`follows` ( `user_id` INT NOT NULL , `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ) ENGINE = InnoDB;
 * */