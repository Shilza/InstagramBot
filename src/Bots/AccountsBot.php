<?php

require_once 'Bot.php';
use InstagramScraper\Instagram;

class AccountsBot extends Bot{
    private $cyclesCount = 0;

    public function __construct(Instagram $instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    public function start(){
        if ($this->followingSelected || $this->likesSelected || $this->commentsSelected) {
            try {
                $account = $this->instagram->getAccount('__diy_._slime__');
                if(!$account->isPrivate())
                    $this->accountProcessing($account);
                else{//TODO: Deleting account from array of genesis accounts

                }
            } catch (\InstagramScraper\Exception\Exception $e) {
                $this->start();
            }
        }
        $this->cyclesCount = 0;
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

    private function isStageFinished(){
        if($this->cyclesCount++ > 1)
            return true;
        else
            return false;
    }

}