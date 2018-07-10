<?php

namespace Bot;

use InstagramAPI\Instagram;
use InstagramAPI\Signatures;

class HashtagBot extends TagBot{
    const STANDARD_HASHTAGS = ['follow4like', "follow4likes", "follow",
        "follow4", "follow4folow", "followers",
        "following", "liker", "likers",
        "likelike", "liked", "likeme", "like4follow", "instalike", "likeit"];

    private $hashtags;

    /**
     * HashtagBot constructor.
     * @param $instagram
     * @param array $settings
     * @param array|null $hashtags
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, array $settings, array $hashtags = null){
        parent::__construct($instagram, $settings);

        if(isset($hashtags)) {
            if($settings['standard_hashtags'])
                $this->hashtags = array_merge($hashtags, static::STANDARD_HASHTAGS);
            else
                $this->hashtags = $hashtags;
        } else if($settings['standard_hashtags'])
            $this->hashtags = static::STANDARD_HASHTAGS;
        else throw new \Exception("No hashtags selected");
    }

    protected function start(){
        $medias = $this->instagram->hashtag->getFeed(
            $this->hashtags[mt_rand(0, count($this->hashtags) - 1)],
            Signatures::generateUUID()
        )->getItems();
        $this->mediaProcessing($medias);
    }
}