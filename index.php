<?php
/**
 * Created by PhpStorm.
 * User: Tolek
 * Date: 30.05.2018
 * Time: 20:36
 */

require 'vendor/autoload.php';

function getUserAndPass(){
    return explode(" ", file_get_contents("config", FILE_USE_INCLUDE_PATH));
}

try {
    $arr = getUserAndPass();

    $instagram = InstagramScraper\Instagram::withCredentials($arr[0], $arr[1]);
    $instagram->login();

    //$instagram->comment('https://www.instagram.com/p/BjcQGPdAwL2/', 'Love it!!!');
} catch(Exception $exception){
    echo "Exception\n";
    echo $exception->getMessage();
}
