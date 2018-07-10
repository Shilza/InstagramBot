<?php

namespace Repository;

use Entity\Account;
use Util\DatabaseWorker;

class AccountsRepository extends Repository implements Updatable {

    const DAY = 86400;

    /**
     * @param array $criterions
     * @return array Account
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
    //TODO DELETE IS NULL
    //TODO REFACTOR QUERY
    public static function getActualAccounts(){
        $time = time();
        $limit =  MAX_PROCESSES_COUNT;
        $query = "SELECT * FROM accounts_queue WHERE
         in_process IS NULL OR (( 
            (time <= $time AND ((end_time IS NULL OR $time < end_time) AND target = 0))
            OR (target = 1 AND $time < comments_limit_time)
            OR (target = 2 AND $time < follows_limit_time)
         )
         AND in_process != true)
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
            $query = "INSERT INTO accounts_queue (id, time, end_time, limit_time, 
                          subscription_end_time, daily_points_count, target, 
                          comments_limit_time, follows_limit_time) 
                      VALUES(:id, :time, $end_time, :limit_time, 
                      :subscription_end_time, :daily_points_count, :target,
                      :comments_limit_time, :follows_limit_time)";

            DatabaseWorker::execute($query, static::accountsDataToArray($account));
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
    static function update($account){
        if ($account instanceof Account) {
            $query = "UPDATE accounts_queue SET 
                time = :time, in_process = :in_process,
                limit_time = :limit_time, daily_points_count = :daily_points_count, 
                subscription_end_time = :subscription_end_time, target = :target,
                comments_limit_time = :comments_limit_time, follows_limit_time = :follows_limit_time"
                .(is_null($account->getEndTime()) ? "" : ", end_time = :end_time")
                ." WHERE id=:id";

            DatabaseWorker::execute($query, static::accountsDataToArray($account));
        }
    }

    /**
     * @param array $accountData
     * @return Account
     */
    private static function dataArrayToAccount(array $accountData)
    {
        return new Account($accountData['id'], $accountData['time'], $accountData['subscription_end_time'],
            $accountData['target'], $accountData['end_time'], $accountData['time_limit'],
            $accountData['in_process'], $accountData['daily_points_count'],
            $accountData['comments_limit_time'], $accountData['follows_limit_time']);
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
            'subscription_end_time' => $account->getSubscriptionEndTime(),
            'target' => $account->getTarget(),
            'comments_limit_time' => $account->getCommentsLimitTime(),
            'follows_limit_time' => $account->getFollowsLimitTime()
        ];

        if(!is_null($account->isInProcess()))
            $accountArray['in_process'] = $account->isInProcess();
        if(!is_null($account->getEndTime()))
            $accountArray['end_time'] = $account->getEndTime();

        return $accountArray;
    }

    //TODO DELETE BY TIME, BECAUSE OF REASONS
    public static function deleteInvalidAccounts(){
        $time = time();
        $query = "DELETE FROM accounts_queue WHERE subscription_end_time < $time";
        DatabaseWorker::execute($query);
    }
}