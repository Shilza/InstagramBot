<?php

namespace Bot;

use InstagramAPI\Exception\NotFoundException;
use InstagramAPI\Instagram;
use InstagramAPI\Response\Model\User;
use InstagramAPI\Signatures;
use Util\DatabaseWorker;

class AccountsBot extends Bot{
    const MAX_ACCOUNTS_COUNT = 20;
    private $cyclesCount = 0;

    /**
     * AccountsBot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, array $settings){
        parent::__construct($instagram, $settings);
    }

    protected function start(){
        $id = $this->getRandomGenesisAccount();
        $this->cyclesCount = 0;
        try {
            $account = $this->instagram->people->getInfoById($id)->getUser();
            if (!$account->getIsPrivate()) {
                $this->accountProcessing($account);
            }
            else {
                DatabaseWorker::execute("DELETE FROM base_accounts WHERE id = $id");
            }
        } catch(NotFoundException $e){
            DatabaseWorker::execute("DELETE FROM base_accounts WHERE id = $id");
        }
    }

    private function getRandomGenesisAccount(){
        return DatabaseWorker::execute(
            "SELECT id FROM base_accounts ORDER BY RAND() LIMIT 1")[0][0];
    }

    /**
     * @param User $account
     * @return bool
     */
    private function accountProcessing(User $account){
        sleep(mt_rand(0, 3));
        if ($this->isStageFinished())
            return true;

        if(!$account->getFollowerCount())
            return false;

        $accounts = $this->instagram->people->getFollowers($account->getPk(),
            Signatures::generateUUID())->getUsers();
        $publicAccounts = $this->getPublicAccounts($accounts);

//        echo "\n Account: " . $account->getUsername() . "\n" . "Accounts: " . "\n";

        $accountsID = [];
        foreach ($publicAccounts as $acc)
            array_push($accountsID, $acc->getPk());
        $this->processing($accountsID);


        if (count($publicAccounts) == 0)
            return false;
        else {
            if (!$this->accountProcessing($publicAccounts[rand(0, count($publicAccounts) - 1)])) {
                foreach ($publicAccounts as $publicAccount)
                    if ($this->accountProcessing($publicAccount))
                        return true;
                return false;
            } else
                return true;
        }
    }

    /**
     * @param User[] $accounts
     * @return User[]
     */
    private function getPublicAccounts(array $accounts){
        $publicAccounts = [];
        $maxCount = static::MAX_ACCOUNTS_COUNT;
        foreach ($accounts as $account) {
            if($maxCount-- <= 0)
                break;

            if (!$account->getIsPrivate())
                array_push($publicAccounts, $account);
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