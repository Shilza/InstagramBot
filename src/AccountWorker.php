<?php

use InstagramScraper\Instagram;
require_once 'src/Repositories/CommentsRepository.php';

class AccountWorker{
    private $instagram;

    public function __construct(Instagram $instagram){
        $this->instagram = $instagram;
    }

    public function unfollowFromAll(){
        $followedUsers = FollowsRepository::getBy([
            'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
            ]);
        foreach ($followedUsers as $followedUser)
            $this->instagram->unfollow($followedUser->getUserId());
    }

    public function unfollowFromUnfollowers(){
        $followedUsers = FollowsRepository::getBy([
            'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
        ]);
        foreach ($followedUsers as $followedUser)
            if(!$this->instagram->getAccount($followedUser->getUserId())->isFollowsViewer())
                $this->instagram->unfollow($followedUser->getUserId());
    }

    public function deleteCommentsByBot(){
        $comments = CommentsRepository::getBy([
            'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
        ]);

        foreach ($comments as $comment) {
            try {
                $this->instagram->deleteComment($comment->getMediaId(), $comment->getId());
            } catch (Exception $e){
                if((strpos($e->getMessage(), "You cannot delete this comment")) === false)
                    throw $e;
            }
            CommentsRepository::delete($comment);
        }
    }

}