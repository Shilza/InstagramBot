<?php

namespace Bot;

use Entity\BotProcessStatistics;
use Exception\WorkStoppedException;
use InstagramAPI\Instagram;
use InstagramAPI\Response\Model\Item;

abstract class TagBot extends Bot{

    /**
     * TagBot constructor.
     * @param Instagram $instagram
     * @param BotProcessStatistics $botProcessStatistics
     * @param array $settings
     * @throws \Exception
     */
    protected function __construct(Instagram $instagram, BotProcessStatistics &$botProcessStatistics, array $settings){
        parent::__construct($instagram, $botProcessStatistics,$settings);
    }

    /**
     * @param Item[] $medias
     * @throws WorkStoppedException
     */
    protected function mediaProcessing(array $medias)
    {
        $accountsID = [];
        foreach ($medias as $media)
            if (!in_array($media->getUser()->getPk(), $accountsID))
                array_push($accountsID, $media->getUser()->getPk());

        $accountsID = array_slice($accountsID, 0, mt_rand(15, 25));
        foreach (
            $this->getPublicAccounts(array_slice(
                    $this->instagram->media->getLikers($medias[0]->getPk())->getUsers(),
                    0, mt_rand(15, 25)
                )
            ) as $acc)
            array_push($accountsID, $acc->getPk());

        $this->processing($accountsID);
    }
}