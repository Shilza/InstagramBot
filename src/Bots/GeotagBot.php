<?php

namespace Bot;

use InstagramScraper\Instagram;

class GeotagBot extends TagBot{

    private $geotags;

    public function __construct(Instagram $instagram, array $settings, array $geotags){
        parent::__construct($instagram, $settings);

        $this->geotags = $geotags;
    }

    /**
     * @return mixed|void
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     * @throws \Unirest\Exception
     */
    protected function start(){
        if(isset($this->geotags)){
            $medias = $this->instagram->getCurrentTopMediasByLocationId(
                $this->instagram->getLocationIdByName($this->geotags[mt_rand(0, count($this->geotags) - 1)]));
            $this->mediaProcessing($medias);
        }
    }

}