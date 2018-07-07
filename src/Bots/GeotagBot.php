<?php

namespace Bot;

use InstagramScraper\Instagram;

class GeotagBot extends TagBot{
    const STANDARD_GEOTAGS = [
        'California', "India", "Kiev"
    ];

    private $geotags;

    /**
     * GeotagBot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @param array|null $geotags
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, array $settings, array $geotags = null){
        parent::__construct($instagram, $settings);

        if(isset($geotags)) {
            if($settings['standard_geotags'])
                $this->geotags = array_merge($geotags, static::STANDARD_GEOTAGS);
            else
                $this->geotags = $geotags;
        } else if($settings['standard_geotags'])
            $this->geotags = static::STANDARD_GEOTAGS;
        else throw new \Exception("No geotags selected");
    }

    /**
     * @return mixed|void
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     */
    protected function start(){
        if(isset($this->geotags)){
            $medias = $this->instagram->getCurrentTopMediasByLocationId(
                $this->instagram->getLocationIdByName($this->geotags[mt_rand(0, count($this->geotags) - 1)]));
            $this->mediaProcessing($medias);
        }
    }

}