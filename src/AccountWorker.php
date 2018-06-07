<?php

use InstagramScraper\Instagram;

class AccountWorker{
    private $instagram;

    public function __construct(Instagram $instagram){
        $this->instagram = $instagram;
    }

    public function unfollowFromAll(){

    }

    public function unfollowFromUnfollowers(){

    }

    public function deleteCommentsByBot(){

    }

}