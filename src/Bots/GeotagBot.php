<?php

require_once 'TagBot.php';
use InstagramScraper\Instagram;

class GeotagBot extends TagBot{

    private $geotags;

    public function __construct(Instagram $instagram, array $settings, array $geotags){
        parent::__construct($instagram, $settings);

        $this->geotags = $geotags;
    }

    public function start(){
        if(isset($this->geotags)){
            $medias = $this->instagram->getCurrentTopMediasByLocationId(
                $this->instagram->getLocationIdByName($this->geotags[mt_rand(0, count($this->geotags) - 1)]));
            $this->mediaProcessing($medias);
        }
    }

}