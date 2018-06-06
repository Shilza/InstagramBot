<?php

require_once 'src/Bot.php';

abstract class TagBot extends Bot{

    protected function __construct($instagram, $settings){
        parent::__construct($instagram, $settings);
    }

    protected function mediaProcessing($medias){
        $accounts = [];
        foreach ($medias as $media)
            if(!static::contains($accounts, $media->getOwnerId()))
                array_push($accounts, $this->instagram->getAccountById($media->getOwnerId()));
        $this->processing($accounts);
    }

    private static function contains($accounts, $accountId){
        foreach ($accounts as $account)
            if($account->getId() == $accountId)
                return true;
        return false;
    }
}