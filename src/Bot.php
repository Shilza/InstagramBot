<?php

abstract class Bot{
    protected $instagram;
    private $commentsText = [];

    protected $isLikesEnabled = false;
    protected $isCommentsEnabled = false;
    protected $isFollowingEnabled = false;

    abstract public function start();

    protected function __construct($instagram, $settings){
        $this->instagram = $instagram;

        if(isset($settings)){
            if(array_key_exists('likes_enabled', $settings))
                $this->isLikesEnabled = $settings['likes_enabled'];
            if(array_key_exists('comments_enabled', $settings))
                $this->isCommentsEnabled = $settings['comments_enabled'];
            if(array_key_exists('following_enabled', $settings))
                $this->isFollowingEnabled = $settings['following_enabled'];
        }

        array_push($this->commentsText, 'Like it!');
        array_push($this->commentsText, 'Nice pic');
        array_push($this->commentsText, 'Awesome ☺');
        array_push($this->commentsText, 'Nice image!!!');
        array_push($this->commentsText, 'Cute ♥');
        array_push($this->commentsText, "👍👍👍");
        array_push($this->commentsText, "🔝🔝🔝");
        array_push($this->commentsText, "🔥🔥🔥");
    }

    protected function processing($accounts){

        foreach ($accounts as $account) {

            $accountObject = (gettype($account) == "object"
                ? $account
                : $this->instagram->getAccountById( $account['id']));

            echo $account->getUsername()."\n";

            if($accountObject->getUsername() != $this->instagram->getSessionUsername()) {

                if ($this->isFollowingEnabled && mt_rand(0, 1) == 1)
                    $this->instagram->follow($accountObject->getId());

                if (!$accountObject->isPrivate()) {
                    if ($this->isLikesEnabled && mt_rand(0, 1) == 1)
                        $this->likeAccountsMedia($accountObject);

                    try {
                        if ($this->isCommentsEnabled && mt_rand(0, 3) == 1)
                            $this->commentAccountsMedia($accountObject);
                    } catch (Exception $e) {
                        if (substr($e->getMessage(), 17, 3) != 403)
                            throw $e;
                        else
                            sleep(3);
                    }
                }
            }
        }
    }

    protected function likeAccountsMedia($accountObject){
        $medias = $this->instagram->getMedias($accountObject->getUsername(), 15);
        $count = mt_rand(3, 5);

        $unlikedMedias = [];
        foreach($medias as $media)
            if(!$media->isLikedByViewer())
                array_push($unlikedMedias, $media);

        if(count($unlikedMedias) > 0) {
            if ($count > count($unlikedMedias))
                foreach ($unlikedMedias as $media)
                    $this->instagram->like($media->getId());
            else
                while ($count > 0) {
                    $index = mt_rand(0, count($unlikedMedias) - 1);
                    $media = $unlikedMedias[$index];

                    if (!$media->isLikedByViewer()) {
                        $this->instagram->like($media->getId());
                        array_splice($unlikedMedias, $index, 1);
                        $count--;
                    }
                }
        }
    }

    protected function commentAccountsMedia($accountObject){
        $medias = $this->instagram->getMedias($accountObject->getUserName(), 5);

        $commentableMedias = [];
        foreach($medias as $media)
            if(!$media->isCommentDisable())
                array_push($commentableMedias, $media);

        if(count($commentableMedias) > 0){ //TODO: Write commentary into DB
            $comment = $this->instagram->comment(
                $commentableMedias[mt_rand(0, count($commentableMedias)-1)]->getId(),
                $this->commentsText[mt_rand(0, count($this->commentsText)-1)]
            );
            echo "Comment: \n ID: ".strval($comment->getId()).' Text: '.strval($comment->getText())
                .' Owner: '.strval($comment->getOwner()).' PicID: '.strval($comment->getPicId())."\n\n";
        }
    }
}