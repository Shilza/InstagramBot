<?php

namespace Bot;

use InstagramAPI\Instagram;
use InstagramAPI\Response\Model\Item;

abstract class TagBot extends Bot{

    protected function __construct(Instagram $instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    /**
     * @param Item[] $medias
     */
    protected function mediaProcessing(array $medias){
        $accountsID = [];
        foreach ($medias as $media)
            if(!in_array($media->getUser()->getPk(), $accountsID))
                array_push($accountsID, $media->getUser()->getPk());

        $this->processing($accountsID);
    }
}