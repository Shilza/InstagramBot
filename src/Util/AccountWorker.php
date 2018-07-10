<?php

namespace Util;

use InstagramAPI\Exception\BadRequestException;
use InstagramAPI\Exception\NotFoundException;
use InstagramAPI\Instagram;
use Repository\CommentsRepository;
use Repository\FollowsRepository;

class AccountWorker
{
    const REQUEST_DELAY = 240;
    const MAX_FAIL_COUNT = 5;
    const MAX_UNFOLLOWS_COUNT = 2000;
    private $failCount = 0;

    private $instagram;

    public function __construct(Instagram $instagram)
    {
        Logger::setFilePath("accountWorker" .$instagram->account_id);
        $this->instagram = $instagram;
    }

    public function unfollowFromAll()
    {
        $this->runFunction('unfollowingFromAll');
    }

    public function unfollowFromUnfollowers()
    {
        $this->runFunction('unfollowingFromUnfollowers');
    }

    public function deleteCommentsByBot()
    {
        $this->runFunction('deletingCommentsByBot');
    }

    /**
     * @param $function
     * @throws BadRequestException
     */
    private function runFunction($function)
    {
        try {
            $this->$function();
        } catch (BadRequestException $e) {
            if ($this->failCount++ < static::MAX_FAIL_COUNT) {
                switch ($e->getCode()) {
                    case 403:
                    case 503:
                        Logger::log("AccountWorker crush: " . $e->getMessage() . PHP_EOL
                            . $e->getTraceAsString());
                        sleep(static::REQUEST_DELAY);
                        $this->unfollowFromAll();
                        break;
                    default:
                        throw $e;
                }
            } else {
                Logger::log("AccountWorker crush: " . $e->getMessage() . PHP_EOL
                    . $e->getTraceAsString());
                $this->runFunction($function);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromAll(){
        $followedUsers = FollowsRepository::getBy(['owner_id' => $this->instagram->account_id]);
        foreach ($followedUsers as $followedUser) {
            $this->instagram->people->unfollow($followedUser->getUserId());
            FollowsRepository::delete($followedUser);
            $this->failCount = 0;
            sleep(mt_rand(12, 22)); //INSTAGRAM LIMITS
        }
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromUnfollowers(){
        $followedUsers = FollowsRepository::getBy(['owner_id' => $this->instagram->account_id]);
        foreach ($followedUsers as $followedUser)
            if(!$this->instagram->people->getFriendship($followedUser->getUserId())->isFollowedBy()){
                $this->instagram->people->unfollow($followedUser->getUserId());
                FollowsRepository::delete($followedUser);
                $this->failCount = 0;
                sleep(mt_rand(12, 22));
            }
    }

    /**
     * @throws \Exception
     */
    private function deletingCommentsByBot()
    {
        $comments = CommentsRepository::getBy(['owner_id' => $this->instagram->account_id]);

        foreach ($comments as $comment) {
            try {
                $this->instagram->media->deleteComment($comment->getMediaId(), $comment->getId());
            }
            catch (NotFoundException $e){
                //Media with given code does not exist or account is private.
                //SKIP. JUST DELETE COMMENT FROM DB
            }
            catch (BadRequestException $e) {
                if ((strpos($e->getMessage(), "You cannot delete this comment")) === false)
                    throw $e;
            }
            CommentsRepository::delete($comment);
            $this->failCount = 0;
        }
    }
}