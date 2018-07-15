<?php

namespace Util;

use InstagramAPI\Exception\BadRequestException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\NotFoundException;
use InstagramAPI\Exception\RequestException;
use InstagramAPI\Instagram;
use Repository\AccountsRepository;
use Repository\CommentsRepository;
use Repository\FollowsRepository;

class AccountWorker
{
    const REQUEST_DELAY = 240;
    const MAX_FAILS_COUNT = 5;

    private $maxPointsCount;
    private $failsCount = 0;
    private $instagram;
    private $target;

    /**
     * AccountWorker constructor.
     * @param Instagram $instagram
     * @param $target
     */
    public function __construct(Instagram $instagram, $target)
    {
        $this->instagram = $instagram;
        $this->target = $target;

        if ($target == 2)
            $this->maxPointsCount = mt_rand(70, 100);
            //$this->maxPointsCount = mt_rand(800, 1000);
        else if ($target > 2)
            $this->maxPointsCount = mt_rand(1800, 2000);
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
     * @throws \Exception
     */
    private function runFunction($function)
    {
        try {
            $this->$function();
        }
        catch (\InstagramAPI\Exception\FeedbackRequiredException $e) {
            if ($e->hasResponse())
                Logger::debug("Bot crush: " . $e->getResponse()->getMessage()
                    . "\n" . $e->getTraceAsString());
        } catch (NetworkException $e) {
            //SKIP SLL ERRORS
        } catch (RequestException $e) {
            if ($this->failsCount++ < static::MAX_FAILS_COUNT) {
                if (stristr($e->getMessage(), "Please wait a few minutes before you try again.") !== false) {
                    Logger::debug("AccountWorker crush: " . $e->getMessage() . PHP_EOL
                        . $e->getTraceAsString());

                    sleep(static::REQUEST_DELAY);

                    Logger::debug("Sleep end");

                    $this->runFunction($function);
                }
                else if (stristr($e->getMessage(), "Not authorized to view user.") === false) {
                    throw $e;
                }
            } else
                throw new \Exception("Request failed");
        } finally {
            $this->failsCount = 0;
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
     * @param array $followedUsers
     * @return bool
     */
    private function unfollow(array $followedUsers){

        for($i = 0; $i < count($followedUsers); $i++){

            //IF WORK IS STOPPED OR MAX LIMIT WAS REACHED
            if(empty(AccountsRepository::getBy(['id' => $this->instagram->account_id,
                'target' => $this->target])) || $this->maxPointsCount-- <= 0)
                return false;

            Logger::trace("Unfollow from " . $followedUsers[$i]->getUserId());
            try {
                $this->instagram->people->unfollow($followedUsers[$i]->getUserId());
            } catch (NotFoundException $e){
                //SKIP DELETED ACCOUNT
            } catch (NetworkException $e) {
                $i--;
            }
            FollowsRepository::delete($followedUsers[$i]);
            $this->failsCount = 0;

            //sleep(mt_rand(12, 22)); //INSTAGRAM LIMITS
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromUnfollowers()
    {
        $allFollowedUsers = FollowsRepository::getBy(['owner_id' => $this->instagram->account_id]);

        for($count = 0; $count * 200 < count($allFollowedUsers); $count++){
            $followedUsers = array_slice($allFollowedUsers, $count * 200, 200);
            $unfollowers = [];

            for ($i = 0; $i < count($followedUsers); $i++)
                try {
                    if (!$this->instagram->people->getFriendship(
                        $followedUsers[$i]->getUserId())->isFollowedBy())
                        array_push($unfollowers, $followedUsers[$i]);
                } catch (NotFoundException $e) {
                    FollowsRepository::delete($followedUsers[$i]);
                } catch (NetworkException $e) {
                    $i--;
                }

            if(!$this->unfollow($unfollowers) || $this->maxPointsCount <= 0)
                return;
        }
    }

    /**
     * @throws \Exception
     */
    private function deletingCommentsByBot()
    {
        $comments = CommentsRepository::getBy(['owner_id' => $this->instagram->account_id]);

        for($i = 0; $i < count($comments); $i++){
            try {
                Logger::trace("Delete comment from " . $comments[$i]->getMediaId());

                $this->instagram->media->deleteComment($comments[$i]->getMediaId(), $comments[$i]->getId());
            } catch (NotFoundException $e) {
                //Media with given code does not exist or account is private.
                //SKIP. JUST DELETE COMMENT FROM DB
            } catch (BadRequestException $e) {
                if ((strpos($e->getMessage(), "You cannot delete this comment")) === false)
                    throw $e;
            } catch (NetworkException $e) {
                //SKIP
            }
            CommentsRepository::delete($comments[$i]);
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