<?php

namespace Bot;

use Entity\BotProcessStatistics;
use Entity\Comment;
use Entity\FollowedUser;
use InstagramAPI\Exception\RequestException;
use InstagramAPI\Instagram;
use Repository\CommentsRepository;
use Repository\FollowsRepository;
use Util\DatabaseWorker;
use Util\Logger;

abstract class Bot
{
    const MAX_FAILS_COUNT = 15;
    const REQUEST_DELAY = 240; //240
    const STANDARD_COMMENTS = ['Like it!', 'Nice pic', 'Awesome ☺',
        'Nice image!!!', 'Cute ♥', "👍👍👍", "🔝🔝🔝", "🔥🔥🔥"];

    protected $instagram;
    private $comments;

    protected $likesSelected = false;
    protected $commentsSelected = false;
    protected $followingSelected = false;

    private $newCommentTime = 0;
    private $newLikeTime = 0;
    private $newFollowTime = 0;

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
                if ($settings['standard_comments']) {
                    $this->comments = array_merge($settings['custom_comments'],
                        static::STANDARD_COMMENTS);
                }
                else
                    $this->comments = $settings['custom_comments'];
            } else if ($settings['standard_comments'])
                $this->comments = static::STANDARD_COMMENTS;
            else throw new \Exception("No comments selected");
        }
    }

    /**
     * @throws \Exception
     * @throws RequestException
     */
    public function run(){
        try {
            if ($this->followingSelected || $this->likesSelected || $this->commentsSelected) {
                Logger::logToConsole("Run " . get_class($this)
                    . " with " . $this->instagram->username);
                $this->start();
            }
        } catch (RequestException $e) {
            if ($this->failsCount++ < static::MAX_FAILS_COUNT)
                switch ($e->getCode()) {
                    case 503:
                    case 403:
                        Logger::log("Bot crush: " . $e->getMessage() . PHP_EOL .
                            $e->getTraceAsString());

                        sleep(static::REQUEST_DELAY);

                        Logger::logToConsole("Sleep end with username "
                            . $this->instagram->username);

                        $this->run();
                        break;
                    default:
                        if (stristr($e->getMessage(), "Not authorized to view user.") === false)
                            throw $e;
                }
            else
                throw new \Exception("Request failed");
        } finally {
            $this->failsCount = 0;
        }
    }

    abstract protected function start();

    /**
     * @param int[] $accountsID
     */
    protected function processing($accountsID){
        foreach ($accountsID as $accountID)
            if ($accountID != $this->instagram->account_id) {

                if ($this->followingSelected && mt_rand(0, 1) == 1) {
                    if(($time = time()) < $this->newFollowTime)
                        sleep($this->newFollowTime - $time);
                    $this->follow($accountID);
                    $this->newFollowTime = time() + mt_rand(28, 38); //DELAY AFTER REQUEST
                }

                if ($this->likesSelected && mt_rand(0, 1) == 1) {
                    if (($time = time()) < $this->newLikeTime)
                        sleep($this->newLikeTime - $time);
                    $this->likeAccountsMedia($accountID);
                    $this->newLikeTime = time() + mt_rand(28, 36); //DELAY AFTER REQUEST
                }

                if ($this->commentsSelected && mt_rand(0, 3) == 1) {
                    if(time() < $this->newCommentTime)
                        continue;
                    $this->commentAccountsMedia($accountID);
                    $this->newCommentTime = time() + mt_rand(200, 250); //DELAY AFTER REQUEST
                }
            }
    }

    /**
     * @param int|string $userID
     */
    protected function likeAccountsMedia($userID){
        $medias = $this->instagram->timeline->getUserFeed($userID)->getItems();
        $count = mt_rand(3, 5);

        if (count($medias) > 0) {

            Logger::logToConsole("Like by " . $this->instagram->username);

            if ($count > count($medias))
                foreach ($medias as $media) {
                    $this->botProcessStatistics->likesCount++;
                    $this->instagram->media->like($media->getPk());
                }
            else
                while ($count > 0) {
                    $index = mt_rand(0, count($medias) - 1);
                    $media = $medias[$index];

                    if (!$media->getHasLiked()) {
                        $this->botProcessStatistics->likesCount++;
                        $this->instagram->media->like($media->getPk());
                        array_splice($medias, $index, 1);
                        $count--;
                    }
                }
        }
    }

    /**
     * @param int|string $userID
     */
    protected function commentAccountsMedia($userID)
    {
        $medias = $this->instagram->timeline->getUserFeed($userID)->getItems();

        $commentableMediasID = [];
        foreach ($medias as $media)
            if (is_null($media->getCommentsDisabled()) //NULL is enabled
                && !$this->commentedByViewer($media->getId()))
                array_push($commentableMediasID, $media->getPk());

        if (count($commentableMediasID) > 0) {
            $this->botProcessStatistics->commentsCount++;
            $comment = $this->instagram->media->comment(
                $commentableMediasID[mt_rand(0, count($commentableMediasID) - 1)],
                $this->comments[mt_rand(0, count($this->comments) - 1)]
            )->getComment();

            CommentsRepository::add(new Comment(
                    $comment->getPk(), $comment->getUser()->getPk(),
                    $comment->getMediaId(), $comment->getText(), $comment->getCreatedAt())
            );

            Logger::logToConsole("Comment by " . $this->instagram->username
                . " on "
                . $this->instagram->media->getInfo(
                    $comment->getMediaId())->getItems()[0]->getUser()->getUsername()
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
     * @param int|string $userID
     */
    private function follow($userID)
    {
        Logger::logToConsole("Follow on "
            . $this->instagram->people->getInfoById($userID)->getUser()->getUsername()
            . " by " . $this->instagram->username);
        $this->botProcessStatistics->followsCount++;
        $this->instagram->people->follow($userID);

        FollowsRepository::add(new FollowedUser($userID, $this->instagram->account_id));
    }

    /**
     * @param int|string $mediaId
     * @return bool
     */
    private function commentedByViewer($mediaId)
    {
        return (DatabaseWorker::execute("SELECT COUNT(media_id) FROM comments"
                . $this->instagram->account_id
                . "WHERE media_id=$mediaId LIMIT 1")[0][0] == 1);
    }
}