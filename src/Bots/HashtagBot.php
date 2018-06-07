<?php

require_once 'TagBot.php';

class HashtagBot extends TagBot{
    private $hashtags;

    public function __construct($instagram, array $settings, array $hashtags){
        parent::__construct($instagram, $settings);

        $this->hashtags = $hashtags;
    }

    public function start(){
        if(isset($this->hashtags)) {
            $medias = $this->instagram->getMediasByTag($this->hashtags[mt_rand(0, count($this->hashtags) - 1)], 5);
            $this->mediaProcessing($medias);
        }
    }
}