<?php

require 'vendor/autoload.php';
require 'src/Bot.php';

function getUserAndPass(){
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

try {
    $arr = getUserAndPass();

    $instagram = InstagramScraper\Instagram::withCredentials($arr[0], $arr[1]);
    $instagram->login();

    $account = $instagram->getAccount('enews');
    sleep(2);
    $bot = new Bot($instagram);
    $bot->start($account);
} catch(Exception $exception){
    echo "Exception\n";
    echo $exception->getMessage();
}
