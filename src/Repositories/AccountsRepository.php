<?php

namespace Repository;

use Entity\Account;
use Util\DatabaseWorker;

class AccountsRepository extends Repository implements Updatable {

    const DAY = 86400;

    /**
     * @param array $criterions
     * @return mixed
     */
    public static function getBy(array $criterions)
    {
        if(count($criterions) > 0) {
            $query = "SELECT * FROM accounts_queue WHERE ";
            foreach ($criterions as $key => $value)
                $query .= "$key='$value' AND ";

            $accountsArray = DatabaseWorker::execute(substr($query, 0, iconv_strlen($query) - 5));
            $accountObjectsArray = [];

            foreach ($accountsArray as $account)
                array_push($accountObjectsArray, static::dataArrayToAccount($account));

            return $accountObjectsArray;
        }
        else
            return null;
    }

    /**
     * @return array
     */
    public static function getAll(){
        $query = "SELECT * FROM accounts_queue ORDER BY time";

        $accountsArray = DatabaseWorker::execute($query);
        $accountObjectsArray = [];

        foreach ($accountsArray as $account)
            array_push($accountObjectsArray, static::dataArrayToAccount($account));

        return $accountObjectsArray;
    }

    /**
     * @return array
     */
    public static function getActualAccounts(){
        $time = time();
        $limit =  MAX_PROCESSES_COUNT;
        $query = "SELECT * FROM accounts_queue WHERE
         in_process IS NULL OR
         (time <= $time AND in_process!=true AND $time > end_time)
         ORDER BY time LIMIT $limit";

        $accountsArray = DatabaseWorker::execute($query);
        $accountObjectsArray = [];

        foreach ($accountsArray as $account)
            array_push($accountObjectsArray, static::dataArrayToAccount($account));

        return $accountObjectsArray;
    }

    /**
     * @param $account
     * @return mixed|void
     */
    public static function add($account)
    {
        if ($account instanceof Account && static::isValid($account->getId())) {
            $end_time = time() + static::DAY;
            $query = "INSERT INTO accounts_queue (id, time, end_time) 
                      VALUES(:id, :time, $end_time)";

            DatabaseWorker::execute($query, static::accountsDataToArray($account));
        }
    }

    /**
     * @param $account
     * @return mixed|void
     */
    public static function delete($account)
    {
        if ($account instanceof Account && static::isValid($account->getId())) {
            $query = "DELETE FROM accounts_queue WHERE id=:id";

            DatabaseWorker::execute($query, ['id' => $account->getId()]);
        }
    }

    /**
     * @param $account
     * @return mixed|void
     */
    static function update($account)
    {
        if ($account instanceof Account) {
            $query = "UPDATE accounts_queue SET 
                time=:time, in_process=:in_process, end_time=:end_time WHERE id=:id";

            DatabaseWorker::execute($query, static::accountsDataToArray($account));
        }
    }

    /**
     * @param array $accountData
     * @return Account
     */
    private static function dataArrayToAccount(array $accountData)
    {
        return new Account($accountData['id'], $accountData['time'],
            $accountData['in_process'], $accountData['end_time']);
    }

    /**
     * @param Account $account
     * @return array
     */
    private static function accountsDataToArray(Account $account)
    {
        $accountArray = ['id' => $account->getId(),
            'time' => $account->getTime(), 'end_time' => $account->getEndTime()];
        if(!is_null($account->isInProcess()))
            $accountArray['in_process'] = $account->isInProcess();

        return $accountArray;
    }

}