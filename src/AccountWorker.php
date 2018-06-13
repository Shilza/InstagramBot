<?php

use InstagramScraper\Instagram;
require_once 'src/Repositories/CommentsRepository.php';

class AccountWorker{
    const REQUEST_DELAY = 60;
    const MAX_FAIL_COUNT = 5;
    private $failCount = 0;

    private $instagram;

    public function __construct(Instagram $instagram){
        $this->instagram = $instagram;
    }

    /**
     * @throws Exception
     */
    public function unfollowFromAll(){
        try {
            $followedUsers = FollowsRepository::getBy([
                'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
            ]);
            foreach ($followedUsers as $followedUser) {
                $this->instagram->unfollow($followedUser->getUserId());
                FollowsRepository::delete($followedUser);
                $this->failCount = 0;
            }
        } catch (\InstagramScraper\Exception\Exception $e){
            if($this->failCount < static::MAX_FAIL_COUNT) {
                sleep(static::REQUEST_DELAY);
                $this->unfollowFromAll();
            } else //TODO
                throw new Exception("Requests failed");
        }
    }

    /**
     * @throws Exception
     */
    public function unfollowFromUnfollowers(){
        try {
            $followedUsers = FollowsRepository::getBy([
                'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
            ]);
            foreach ($followedUsers as $followedUser)
                if (!$this->instagram->getAccount($followedUser->getUserId())->isFollowsViewer()) {
                    $this->instagram->unfollow($followedUser->getUserId());
                    FollowsRepository::delete($followedUser);
                    $this->failCount = 0;
                }
        } catch (\InstagramScraper\Exception\Exception $e){
            if($this->failCount < static::MAX_FAIL_COUNT) {
                sleep(static::REQUEST_DELAY);
                $this->unfollowFromUnfollowers();
            } else //TODO
                throw new Exception("Requests failed");
        }

    }

    /**
     * @throws Exception
     */
    public function deleteCommentsByBot(){
        try {
            $comments = CommentsRepository::getBy([
                'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
            ]);

            foreach ($comments as $comment) {
                try {
                    $this->instagram->deleteComment($comment->getMediaId(), $comment->getId());
                } catch (\InstagramScraper\Exception\Exception $e) {
                    if ((strpos($e->getMessage(), "You cannot delete this comment")) === false)
                        throw $e;
                }
                CommentsRepository::delete($comment);
                $this->failCount = 0;
            }
        } catch (\InstagramScraper\Exception\Exception $e){
            if($this->failCount < static::MAX_FAIL_COUNT) {
                sleep(static::REQUEST_DELAY);
                $this->deleteCommentsByBot();
            } else //TODO
                throw new Exception("Requests failed");
        }
    }

}