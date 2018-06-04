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
            if (!in_array($item->getOwner(), $accounts))
                array_push($accounts, $this->instagram->getAccountById($item->getOwnerId()));
        $this->processing($accounts);

    }

}