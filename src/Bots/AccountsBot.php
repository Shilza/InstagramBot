<?php

namespace Bot;

use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Instagram;
use InstagramScraper\Model\Account;

class AccountsBot extends Bot{
    private $cyclesCount = 0;

    public function __construct(Instagram $instagram, array $settings)
    {
        parent::__construct($instagram, $settings);
    }

    /**
     * @return mixed|void
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     */
    protected function start(){
        $nickname = $this->getRandomGenesisAccount();
        $this->cyclesCount = 0;
        try {
            $account = $this->instagram->getAccount($nickname);
            if (!$account->isPrivate())
                $this->accountProcessing($account);
            else {//TODO: Deleting account from array of genesis accounts
            }
        } catch(InstagramNotFoundException $e){//TODO
        }
    }

    //TODO
    private function getRandomGenesisAccount(){
        return "";
    }

    /**
     * @param Account $account
     * @param int $limit
     * @return bool
     * @throws InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramRequestException
     * @throws \Unirest\Exception
     */
    private function accountProcessing(Account $account, $limit = 10)
    {
        sleep(mt_rand(0, 3));
        if ($this->isStageFinished())
            return true;

        $count = $account->getFollowedByCount();

        $nextCount = ($count > $limit ? $limit : $count);
        echo "Next count: " . strval($nextCount);

        $accounts = $this->instagram->getFollowers($account->getId(), $nextCount, ($nextCount < 20 ? $nextCount : 20));
        $publicAccounts = $this->getPublicAccounts($accounts);

        echo "\n Account: " . $account->getUsername() . "\n" . "Accounts: " . "\n";
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

    /**
     * @param array $accounts
     * @return array
     * @throws InstagramNotFoundException
     * @throws \InstagramScraper\Exception\InstagramException
     */
    private function getPublicAccounts(array $accounts)
    {
        $publicAccounts = [];
        foreach ($accounts as $acc) {
            $acc = $this->instagram->getAccountById($acc['id']);
            if (!$acc->isPrivate())
                array_push($publicAccounts, $acc);
        }
        return $publicAccounts;
    }

    /**
     * @return bool
     */
    private function isStageFinished()
    {
        if ($this->cyclesCount++ > 1)
            return true;
        else
            return false;
    }

}