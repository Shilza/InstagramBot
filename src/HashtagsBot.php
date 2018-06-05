<?php

require_once 'src/Bot.php';

class HashtagsBot extends Bot{
    private $hashtags;

    public function __construct($instagram, $settings, $hashtags){
        parent::__construct($instagram, $settings);

        $this->hashtags = $hashtags;
    }

    public function start(){
        if(isset($this->hashtags))
            $this->processing1();
    }

    public function processing1(){
        $medias = $this->instagram->getMediasByTag($this->hashtags[mt_rand(0, count($this->hashtags) - 1)], 5);
        $accounts = [];
        foreach ($medias as $item)
            if(!static::contains($accounts, $item->getOwnerId()))
                array_push($accounts, $this->instagram->getAccountById($item->getOwnerId()));
        $this->processing($accounts);
    }

    private static function contains($accounts, $accountId){
        foreach ($accounts as $account)
            if($account->getId() == $accountId)
                return true;
        return false;
    }
}