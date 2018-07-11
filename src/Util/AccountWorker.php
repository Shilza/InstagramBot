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
    private $maxPointsCount;

    private $failsCount = 0;

    private $instagram;

    /**
     * AccountWorker constructor.
     * @param Instagram $instagram
     * @param $target
     */
    public function __construct(Instagram $instagram, $target)
    {
        $this->instagram = $instagram;

        if ($target == 2)
            $this->maxPointsCount = mt_rand(20, 30);
            //$this->maxPointsCount = mt_rand(800, 1000);
        else if ($target > 2)
            $this->maxPointsCount = mt_rand(20, 30);
            //$this->maxPointsCount = mt_rand(1800, 2000);
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
        }
        catch (BadRequestException $e) {
            if ($this->failsCount++ < static::MAX_FAIL_COUNT) {
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
    private function unfollowingFromAll()
    {
        $this->unfollow(FollowsRepository::getBy(['owner_id' => $this->instagram->account_id]));
    }

    /**
     * @param $followedUsers
     */
    private function unfollow(array $followedUsers){
        foreach ($followedUsers as $followedUser) {
            Logger::logToConsole("Unfollow from " . $followedUser->getUserId()
                . " by " . $followedUser->getOwnerId());
            try {
                $this->instagram->people->unfollow($followedUser->getUserId());
            } catch (NotFoundException $e){
                //SKIP DELETED ACCOUNT
            }
            FollowsRepository::delete($followedUser);
            $this->failsCount = 0;

            if (--$this->maxPointsCount <= 0)
                return;
            //sleep(mt_rand(12, 22)); //INSTAGRAM LIMITS
        }
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromUnfollowers()
    {
        $followedUsers = FollowsRepository::getBy(['owner_id' => $this->instagram->account_id]);
        $unfollowers = [];
        foreach ($followedUsers as $followedUser)
            if (!$this->instagram->people->getFriendship($followedUser->getUserId())->isFollowedBy())
                array_push($unfollowers, $followedUser);

        $this->unfollow($unfollowers);
    }

    /**
     * @throws \Exception
     */
    private function deletingCommentsByBot()
    {
        $comments = CommentsRepository::getBy(['owner_id' => $this->instagram->account_id]);

        foreach ($comments as $comment) {
            try {
                Logger::logToConsole("Delete comment from " . $comment->getMediaId()
                    . " by " . $comment->getOwnerId());

                $this->instagram->media->deleteComment($comment->getMediaId(), $comment->getId());
            } catch (NotFoundException $e) {
                //Media with given code does not exist or account is private.
                //SKIP. JUST DELETE COMMENT FROM DB
            } catch (BadRequestException $e) {
                if ((strpos($e->getMessage(), "You cannot delete this comment")) === false)
                    throw $e;
            }
            CommentsRepository::delete($comment);
            $this->failsCount = 0;

            if (--$this->maxPointsCount <= 0)
                return;
            //sleep(mt_rand(12, 22));
        }
    }

    /**
     * @return int
     */
    public function getMaxPointsCount(): int
    {
        return $this->maxPointsCount;
    }
}