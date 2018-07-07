<?php

namespace Bot;

use Entity\BotProcessStatistics;
use Entity\Comment;
use Entity\FollowedUser;
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramRequestException;
use InstagramScraper\Instagram;
use InstagramScraper\Model\Account;

use Repository\CommentsRepository;
use Repository\FollowsRepository;
use Unirest;
use Util\DatabaseWorker;
use Util\Logger;

abstract class Bot{
    const MAX_FAILS_COUNT = 15;
    const REQUEST_DELAY = 240; //240

    protected $instagram;
    const  STANDARD_COMMENTS = ['Like it!', 'Nice pic', 'Awesome ☺',
        'Nice image!!!', 'Cute ♥', "👍👍👍", "🔝🔝🔝", "🔥🔥🔥"];
    private $comments;

    protected $likesSelected = false;
    protected $commentsSelected = false;
    protected $followingSelected = false;

    private $failsCount = 0;

    private $botProcessStatistics;

    /**
     * Bot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @throws \Exception
     */
    protected function __construct(Instagram $instagram, array $settings)
    {
        $this->instagram = $instagram;
        $this->botProcessStatistics = new BotProcessStatistics();

        if (isset($settings)) {
            if (array_key_exists('likes', $settings))
                $this->likesSelected = $settings['likes'];
            if (array_key_exists('comments', $settings))
                $this->commentsSelected = $settings['comments'];
            if (array_key_exists('followings', $settings))
                $this->followingSelected = $settings['followings'];

            if (isset($settings['custom_comments'])) {
                if ($settings['standard_comments'])
                    $this->comments = array_merge($settings['custom_comments'],
                        static::STANDARD_COMMENTS);
                else
                    $this->comments = $settings['custom_comments'];
            } else if ($settings['standard_comments'])
                $this->comments = static::STANDARD_COMMENTS;
            else throw new \Exception("No comments selected");
        }
    }

    /**
     * @throws InstagramRequestException
     * @throws \Exception
     */
    public function run()
    {
        Logger::logToConsole("Run " . get_class($this)
            . " with " . $this->instagram->getSessionUsername());
        try {
            if ($this->followingSelected || $this->likesSelected || $this->commentsSelected)
                $this->start();
        } catch (InstagramRequestException $e) {
            if ($this->failsCount++ < static::MAX_FAILS_COUNT)
                switch ($e->getCode()) {
                    case 503:
                    case 403:
                        Logger::log("Bot crush: " . $e->getMessage() . PHP_EOL .
                            $e->getTraceAsString());

                        sleep(static::REQUEST_DELAY);

                        Logger::logToConsole("Sleep end with username "
                            . $this->instagram->getSessionUsername());

                        $this->run();
                        break;
                    default:
                        throw $e;
                }
            else
                throw new \Exception("Request failed");
        } catch (InstagramException $e) {
            if (stristr($e->getMessage(), "The account is private") === false)
                throw $e;

            return;
        } catch (Unirest\Exception $e) {
            Logger::log("Bot crush: " . $e->getMessage() . PHP_EOL .
                $e->getTraceAsString());
            $this->run();
        } finally {
            $this->failsCount = 0;
        }
    }

    /**
     * @return mixed
     * @throws InstagramRequestException
     */
    abstract protected function start();

    /**
     * @param $accounts
     * @throws \Exception
     * @throws InstagramRequestException
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     */
    protected function processing($accounts)
    {
        foreach ($accounts as $account) {

            $accountObject = (gettype($account) == "object"
                ? $account
                : $this->instagram->getAccountById($account['id']));

            if ($accountObject->getUsername() != $this->instagram->getSessionUsername()) {

                if ($this->followingSelected && mt_rand(0, 1) == 1)
                    $this->follow($accountObject);

                if (!$accountObject->isPrivate()) {
                    if ($this->likesSelected && mt_rand(0, 1) == 1)
                        $this->likeAccountsMedia($accountObject);

                    if ($this->commentsSelected && mt_rand(0, 3) == 1)
                        $this->commentAccountsMedia($accountObject);
                }
            }
        }
    }

    /**
     * @param $accountObject
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     */
    protected function likeAccountsMedia($accountObject)
    {
        $medias = $this->instagram->getMedias($accountObject->getUsername(), 15);
        $count = mt_rand(3, 5);

        if (count($medias) > 0) {

            Logger::logToConsole("Like by " . $this->instagram->getSessionUsername());

            if ($count > count($medias))
                foreach ($medias as $media) {
                    $this->botProcessStatistics->likesCount++;
                    $this->instagram->like($media->getId());
                }
            else
                while ($count > 0) {
                    $index = mt_rand(0, count($medias) - 1);
                    $media = $medias[$index];

                    if (!$media->isLikedByViewer()) {
                        $this->botProcessStatistics->likesCount++;
                        $this->instagram->like($media->getId());
                        array_splice($medias, $index, 1);
                        $count--;
                    }
                }
        }
    }

    /**
     * @param $accountObject
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     */
    protected function commentAccountsMedia($accountObject)
    {
        $medias = $this->instagram->getMedias($accountObject->getUserName(), 5);

        $commentableMedias = [];
        foreach ($medias as $media)
            if (!$media->isCommentDisable() && !$this->commentedByViewer($media->getId()))
                array_push($commentableMedias, $media);

        if (count($commentableMedias) > 0) {
            $this->botProcessStatistics->commentsCount++;
            $comment = $this->instagram->comment(
                $commentableMedias[mt_rand(0, count($commentableMedias) - 1)]->getId(),
                $this->comments[mt_rand(0, count($this->comments) - 1)]
            );

            CommentsRepository::add(new Comment(
                    $comment->getId(), $comment->getOwner()->getId(),
                    $comment->getPicId(), $comment->getText(), $comment->getCreatedAt())
            );

            Logger::logToConsole("Comment by " . $this->instagram->getSessionUsername()
                . " on "
                . $this->instagram->getMediaById($comment->getPicId())->getOwner()->getUsername()
                . " Text: " . $comment->getText());
        }
    }

    /**
     * @return BotProcessStatistics
     */
    public function getBotProcessStatistics()
    {
        return $this->botProcessStatistics;
    }

    public function resetBotProcessStatistics()
    {
        $this->botProcessStatistics->likesCount = 0;
        $this->botProcessStatistics->commentsCount = 0;
        $this->botProcessStatistics->followsCount = 0;
    }

    /**
     * @param Account $account
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     */
    private function follow(Account $account)
    {
        Logger::logToConsole("Follow on " . $account->getUsername()
            . " by " . $this->instagram->getSessionUsername());

        $this->botProcessStatistics->followsCount++;
        $this->instagram->follow($account->getId());

        FollowsRepository::add(new FollowedUser($account->getId(),
            $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()));
    }

    /**
     * @param $mediaId
     * @return bool
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     */
    private function commentedByViewer($mediaId)
    {
        return (DatabaseWorker::execute("SELECT COUNT(media_id) FROM comments"
                . $this->instagram->getAccount($this->instagram->getSessionUsername())->getId()
                . " WHERE media_id=$mediaId LIMIT 1")[0][0] == 1);
    }
}