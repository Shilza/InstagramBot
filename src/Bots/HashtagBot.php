<?php

namespace Bot;

class HashtagBot extends TagBot{
    const DEFAULT_HASHTAGS = ['follow4like', "follow4likes", "follow",
        "follow4", "follow4folow", "followers",
        "following", "liker", "likers",
        "likelike", "liked", "likeme", "like4follow", "instalike", "likeit"];

    public function __construct($instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    /**
     * @return mixed|void
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     */
    protected function start(){
        $medias = $this->instagram->getMediasByTag(
            static::DEFAULT_HASHTAGS[mt_rand(0, count(static::DEFAULT_HASHTAGS) - 1)], 20);
        $this->mediaProcessing($medias);
    }
}