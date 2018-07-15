<?php

namespace Repository;

use Entity\Account;
use Util\DatabaseWorker;

class AccountsRepository extends Repository implements Updatable {

    const DAY = 86400;

    /**
     * @param array $criterions
     * @return Account[]|null
     */
    public static function getBy(array $criterions)
    {
        if(count($criterions) > 0) {
            $query = "SELECT * FROM accounts_queue WHERE ";
            foreach ($criterions as $key => $value)
                $query .= "$key='$value' AND ";

            $accountsArray = DatabaseWorker::execute(
                substr($query, 0, iconv_strlen($query) - 5));
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
        $query = "SELECT * FROM accounts_queue WHERE (in_process IS NULL AND target > 0) OR(
          in_process = false AND time <= $time
          AND (
            ($time < end_time AND target = 1)
            OR ($time > limit_time AND (target = 2 OR target = 3))
          ))
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
            $query = "INSERT INTO accounts_queue (id, time, end_time, limit_time, 
                      daily_points_count, target) 
                      VALUES(:id, :time, :end_time, :limit_time, :daily_points_count, :target)";

            $arr = static::accountsDataToArray($account);
            array_splice($arr,
                array_search('old_target', array_keys($arr), true), 1);
            DatabaseWorker::execute($query, $arr);
        }
    }

    /**
     * @param $account
     */
    public static function delete($account)
    {
        if ($account instanceof Account && static::isValid($account->getId())) {
            $query = "DELETE FROM accounts_queue WHERE id=:id";

            DatabaseWorker::execute($query, ['id' => $account->getId()]);
        }
    }

    /**
     * @param Account $account
     */
    static function update(&$account){
        if ($account instanceof Account) {
            $query = "UPDATE accounts_queue SET 
                time = :time, in_process = :in_process,
                limit_time = :limit_time, daily_points_count = :daily_points_count, 
                target = :target, end_time = :end_time
                WHERE id=:id AND target=:old_target";

            DatabaseWorker::execute($query, static::accountsDataToArray($account));
        }
    }

    /**
     * @param array $accountData
     * @return Account
     */
    private static function dataArrayToAccount(array $accountData)
    {
        return new Account($accountData['id'],
            $accountData['target'], $accountData['end_time'], $accountData['time'],
            $accountData['limit_time'], $accountData['in_process'], $accountData['daily_points_count']);
    }

    /**
     * @param Account $account
     * @return array Account
     */
    private static function accountsDataToArray(Account $account)
    {
        $accountArray = [
            'id' => $account->getId(),
            'time' => $account->getTime(),
            'limit_time' => $account->getLimitTime(),
            'daily_points_count' => $account->getDailyPointsCount(),
            'target' => $account->getTarget(),
            'end_time' => $account->getEndTime(),
            'old_target' => $account->getOldTarget()
        ];

        if(!is_null($account->isInProcess()))
            $accountArray['in_process'] = $account->isInProcess();

        return $accountArray;
    }

    public static function deleteInvalidAccounts(){
        $time = time();

        $query = "DELETE FROM accounts_queue WHERE
                    ((SELECT subscription_end_time FROM users WHERE accounts_queue.id = users.id LIMIT 1) 
                    < $time AND limit_time < $time)
                    OR (target < 0 AND limit_time < $time)";
        DatabaseWorker::execute($query);
    }
}