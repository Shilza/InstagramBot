<?php

namespace Bot;

use Entity\BotProcessStatistics;
use Exception\WorkStoppedException;
use InstagramAPI\Instagram;

class GeotagBot extends TagBot{
    const STANDARD_GEOTAGS = [
        'California', "India", "Kiev"
    ];

    private $geotags;

    /**
     * GeotagBot constructor.
     * @param Instagram $instagram
     * @param BotProcessStatistics $botProcessStatistics
     * @param array $settings
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, BotProcessStatistics &$botProcessStatistics, array $settings){
        parent::__construct($instagram, $botProcessStatistics, $settings);

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
     * @throws WorkStoppedException
     */
    protected function start(){
        if(isset($this->geotags)){
            $result = $this->instagram->location->findPlaces(
                $this->geotags[mt_rand(0, count($this->geotags) - 1)]);

            $this->mediaProcessing($this->instagram->location->getFeed(
                $result->getItems()[0]->getLocation()->getPk(),
                $result->getRankToken()
            )->getItems()
            );
        }
    }

}