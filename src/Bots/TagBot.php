<?php

require_once 'Bot.php';
use InstagramScraper\Instagram;

abstract class TagBot extends Bot{

    protected function __construct(Instagram $instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    protected function mediaProcessing(array $medias){
        $accounts = [];
        foreach ($medias as $media)
            if(!static::contains($accounts, $media->getOwnerId()))
                array_push($accounts, $this->instagram->getAccountById($media->getOwnerId()));
        $this->processing($accounts);
    }

    private static function contains(array $accounts, $accountId){
        foreach ($accounts as $account)
            if($account->getId() == $accountId)
                return true;
        return false;
    }
}