<?php

class Bot{
    private $instagram;
    private $commentsText = [];
    ////////////////////////Temporary
    private $tempCount = 0;

    public function __construct($instagram){
        $this->instagram = $instagram;

        array_push($this->commentsText, 'Like it!');
        array_push($this->commentsText, 'Nice pic');
        array_push($this->commentsText, 'Awesome ☺');
        array_push($this->commentsText, 'Nice image!!!');
        array_push($this->commentsText, 'Cute ♥');
    }

    private function processing($accounts){
        foreach ($accounts as $account) {
            echo $account['username'] . "\n";
            $accountObject = $this->instagram->getAccountById($account['id']);

            if(mt_rand(0, 1) == 1)
                $this->instagram->follow($accountObject->getId());

            if(!$accountObject->isPrivate()){
                if(mt_rand(0, 1) == 1)
                    $this->likeAccountsMedia($accountObject);

                if(mt_rand(0, 1) == 1)
                    $this->commentAccountsMedia($accountObject);
            }
        }
    }

    public function start($account, $limit = 10){
        sleep(mt_rand(0, 3));
        if ($this->isStageFinished())
            return true;

        $count = $account->getFollowsCount();

        $nextCount = ($count > $limit ? $limit : $count);
        echo "Next count: ".strval($nextCount);
        $accounts = $this->instagram->getFollowing($account->getId(), $nextCount, ($nextCount < 20 ? $nextCount : 20));
        $publicAccounts = $this->getPublicAccounts($accounts);

        echo "\n Account: ".$account->getUsername()."\n"."Accounts: "."\n";
        $this->processing($accounts);

        if ($count > $limit) {
            if (count($publicAccounts) == 0)
                return $this->start($account, $limit * 2 > $count ? $count : $limit * 2);
            else {
                if (!$this->start($publicAccounts[rand(0, count($publicAccounts) - 1)])) {
                    foreach ($publicAccounts as $publicAccount)
                        if ($this->start($publicAccount))
                            return true;
                    return false;
                } else
                    return true;
            }
        } else if (count($publicAccounts) == 0)
            return false;
        else if (!$this->start($publicAccounts[rand(0, count($publicAccounts) - 1)])) {
            foreach ($publicAccounts as $publicAccount)
                if ($this->start($publicAccount))
                    return true;
            return false;
        } else
            return true;
    }

    private function getPublicAccounts($accounts){
        $publicAccounts = [];
        foreach ($accounts as $acc) {
            $acc = $this->instagram->getAccountById($acc['id']);
            if (!$acc->isPrivate())
                array_push($publicAccounts, $acc);
        }
        return $publicAccounts;
    }

    //TODO:
    private function isStageFinished(){
        echo "Stage finished?\n";
        if($this->tempCount++ > 1)
            return true;
        else
            return false;
    }

    private function likeAccountsMedia($accountObject){
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
                    if(is_null($media)) {
                        echo "NULL!!!".strval(count($unlikedMedias))." ".strval($index)."\n";
                        return;
                    }

                    if (!$media->isLikedByViewer()) {
                        $this->instagram->like($media->getId());
                        array_splice($unlikedMedias, $index, 1);
                        $count--;
                    }
                }
        }
    }

    private function commentAccountsMedia($accountObject){
        $medias = $this->instagram->getMedias($accountObject->getUserName(), 5);

        $commentableMedias = [];
        foreach($medias as $media)
            if(!$media->isCommentDisable())
                array_push($commentableMedias, $media);

        if(count($commentableMedias) > 0){ //TODO: Write commentary into DB
            $comment = $this->instagram->comment(
                $commentableMedias[mt_rand(0, count($commentableMedias))]->getId(),
                $this->commentsText[mt_rand(0, count($this->commentsText))]
            );
            echo "Comment: \n ID: ".strval($comment->getId()).' Text: '.strval($comment->getText())
                .' Owner: '.strval($comment->getOwner()).' PicID: '.strval($comment->getPicId())."\n\n";
        }
    }
}