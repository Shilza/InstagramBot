<?php

namespace Util;

use InstagramScraper\Exception\Exception;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Exception\InstagramRequestException;
use InstagramScraper\Instagram;
use Repository\CommentsRepository;
use Repository\FollowsRepository;
use Unirest;

class AccountWorker
{
    const REQUEST_DELAY = 240;
    const MAX_FAIL_COUNT = 5;
    private $failCount = 0;

    private $instagram;

    public function __construct(Instagram $instagram)
    {
        Logger::setFilePath("accountWorker"
            .$instagram->getAccount($instagram->getSessionUsername())->getId());
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
     * @throws Exception
     */
    private function runFunction($function)
    {
        try {
            $this->$function();
        } catch (Exception $e) {
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
        } catch (Unirest\Exception $e) {
            Logger::log("AccountWorker crush: " . $e->getMessage() . PHP_EOL
                . $e->getTraceAsString());
            $this->runFunction($function);
        }
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromAll()
    {
        $followedUsers = FollowsRepository::getBy([
            'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
        ]);
        foreach ($followedUsers as $followedUser) {
            $this->instagram->unfollow($followedUser->getUserId());
            FollowsRepository::delete($followedUser);
            $this->failCount = 0;
        }
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromUnfollowers()
    {
        $followedUsers = FollowsRepository::getBy([
            'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
        ]);
        foreach ($followedUsers as $followedUser)
            if (!$this->instagram->getAccount($followedUser->getUserId())->isFollowsViewer()) {
                $this->instagram->unfollow($followedUser->getUserId());
                FollowsRepository::delete($followedUser);
                $this->failCount = 0;
            }
    }

    /**
     * @throws \Exception
     */
    private function deletingCommentsByBot()
    {
        $comments = CommentsRepository::getBy([
            'owner_id' => $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
        ]);

        foreach ($comments as $comment) {
            try {
                $this->instagram->deleteComment($comment->getMediaId(), $comment->getId());
            }
            catch (InstagramNotFoundException $e){
                //Media with given code does not exist or account is private.
                //SKIP. JUST DELETE COMMENT FROM DB
            }
            catch (InstagramRequestException $e) {
                if ((strpos($e->getMessage(), "You cannot delete this comment")) === false)
                    throw $e;
            }
            CommentsRepository::delete($comment);
            $this->failCount = 0;
        }
    }
}