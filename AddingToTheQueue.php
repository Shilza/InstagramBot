<?php

require 'vendor/autoload.php';

use InstagramScraper\Instagram;

const MAX_HASHTAG_LENGTH = 20;

$settings = ['hashtags' => "aaaaaajhdijghdksjrghjdhg liker liked follow4follow like"];

//$login = 0;
//$password = 0;
//$instagram = Instagram::withCredentials($login, $password);
//$instagram->login();

if(array_key_exists('hashtags', $settings)){
    if(strlen($settings['hashtags']) < 400) {
        $hashtags = explode(";", $settings['hashtags']);
        if (count($hashtags) > 0 && count($hashtags) <= 20){
            $deletedHashtags = [];
            foreach($hashtags as &$hashtag){
                $findedHashtag = Instagram::searchTagsByTagName($hashtag)[0];
                if(is_null($findedHashtag) || $findedHashtag->getName() != $hashtag
                    || $findedHashtag->getMediaCount() < 200000)
                    array_push($deletedHashtags, $hashtag);
            }
            $hashtags = array_diff($hashtags, $deletedHashtags);
        } else { //TODO: send error message

        }
    } else { //TODO: send error message

    }
}

if(array_key_exists('geotags', $settings)){
    if(strlen($settings['geotags']) < 400) {
        $hashtags = explode(" ", $settings['hashtags']);
        if (count($hashtags) > 0 && count($hashtags) <= 20){
            $deletedHashtags = [];
            foreach($hashtags as &$hashtag){
                $findedHashtag = Instagram::searchTagsByTagName($hashtag)[0];
                if(is_null($findedHashtag) || $findedHashtag->getName() != $hashtag
                    || $findedHashtag->getMediaCount() < 200000)
                    array_push($deletedHashtags, $hashtag);
            }
            $hashtags = array_diff($hashtags, $deletedHashtags);
        } else { //TODO: send error message

        }
    } else { //TODO: send error message

    }
}