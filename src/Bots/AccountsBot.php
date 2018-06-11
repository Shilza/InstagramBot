<?php

require_once 'Bot.php';
use InstagramScraper\Instagram;

class AccountsBot extends Bot{
    ////////////////////////Temporary
    private $tempCount = 0;

    public function __construct(Instagram $instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    public function start()
    {
        if ($this->isFollowingEnabled || $this->isLikesEnabled || $this->isCommentsEnabled) {
            try {
                $this->accountProcessing($this->instagram->getAccount('anthonellaruizdiaz'));
            } catch (Exception $e) {
                $this->start();
            }
        }
        $this->tempCount = 0;
    }

    private function accountProcessing($account, $limit = 10){
        sleep(mt_rand(0, 3));
        if ($this->isStageFinished())
            return true;

        $count = $account->getFollowedByCount();

        $nextCount = ($count > $limit ? $limit : $count);
        echo "Next count: ".strval($nextCount);
        $accounts = $this->instagram->getFollowers($account->getId(), $nextCount, ($nextCount < 20 ? $nextCount : 20));
        $publicAccounts = $this->getPublicAccounts($accounts);

        echo "\n Account: ".$account->getUsername()."\n"."Accounts: "."\n";
        $this->processing($accounts);

        if ($count > $limit) {
            if (count($publicAccounts) == 0)
                return $this->accountProcessing($account, $limit * 2 > $count ? $count : $limit * 2);
            else {
                if (!$this->accountProcessing($publicAccounts[rand(0, count($publicAccounts) - 1)])) {
                    foreach ($publicAccounts as $publicAccount)
                        if ($this->accountProcessing($publicAccount))
                            return true;
                    return false;
                } else
                    return true;
            }
        } else if (count($publicAccounts) == 0)
            return false;
        else if (!$this->accountProcessing($publicAccounts[rand(0, count($publicAccounts) - 1)])) {
            foreach ($publicAccounts as $publicAccount)
                if ($this->accountProcessing($publicAccount))
                    return true;
            return false;
        } else
            return true;
    }

    private function getPublicAccounts(array $accounts){
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
        if($this->tempCount++ > 1)
            return true;
        else
            return false;
    }

}