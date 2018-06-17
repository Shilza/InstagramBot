<?php

namespace Bot;

use InstagramScraper\Instagram;

abstract class TagBot extends Bot{

    protected function __construct(Instagram $instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    /**
     * @param array $medias
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     * @throws \Unirest\Exception
     */
    protected function mediaProcessing(array $medias){
        $accounts = [];
        foreach ($medias as $media)
            if(!static::contains($accounts, $media->getOwnerId()))
                array_push($accounts, $this->instagram->getAccountById($media->getOwnerId()));
        $this->processing($accounts);
    }

    /**
     * @param array $accounts
     * @param $accountId
     * @return bool
     */
    private static function contains(array $accounts, $accountId){
        foreach ($accounts as $account)
            if($account->getId() == $accountId)
                return true;
        return false;
    }
}