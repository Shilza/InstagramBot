<?php

namespace Repository;


use Entity\BotProcessStatistics;
use Util\DatabaseWorker;

class StatisticsRepository extends Repository implements Updatable
{

    /**
     * @param array $criterions
     * @return BotProcessStatistics|mixed|null
     */
    public static function getBy(array $criterions)
    {
        if(key_exists('id', $criterions) && static::isValid($criterions['id'])){
            $id = $criterions['id'];
            $query = "SELECT * FROM accounts_queue WHERE id = $id";

            $statisticsArray = DatabaseWorker::execute($query)[0];

            return static::dataArrayToStatistics($statisticsArray);
        }
        else
            return null;
    }

    /**
     * @param $statistics
     * @return mixed|void
     */
    public static function add($statistics)
    {
        if ($statistics instanceof BotProcessStatistics && static::isValid($statistics->id)) {
            $query = "INSERT INTO statistics (id, likes_count, comments_count, follows_count) 
                      VALUES(:id, :likes_count, :comments_count, :follows_count)";

            DatabaseWorker::execute($query, static::statisticsDataToArray($statistics));
        }
    }

    /**
     * @param $statistics
     * @return mixed|void
     */
    public static function delete($statistics)
    {
        if ($statistics instanceof BotProcessStatistics && static::isValid($statistics->id)) {
            $query = "DELETE FROM statistics WHERE id=:id";

            DatabaseWorker::execute($query, ['id' => $statistics->id]);
        }
    }

    /**
     * @param $statistics
     * @return mixed|void
     */
    static function update($statistics)
    {
        if ($statistics instanceof BotProcessStatistics) {
            $query = "UPDATE statistics SET likes_count=:likes_count,
                        comments_count=:comments_count, follows_count=:follows_count WHERE id=:id";

            DatabaseWorker::execute($query, static::statisticsDataToArray($statistics));
        }
    }

    /**
     * @param $statistics
     */
    static function addPoints($statistics){
        if ($statistics instanceof BotProcessStatistics) {
            $query = "UPDATE statistics SET likes_count = likes_count + :likes_count,
                        comments_count = comments_count + :comments_count,
                        follows_count = follows_count + :follows_count WHERE id=:id";
            
            DatabaseWorker::execute($query, static::statisticsDataToArray($statistics));
        }
    }

    /**
     * @param $statisticsArray
     * @return BotProcessStatistics
     */
    private static function dataArrayToStatistics($statisticsArray)
    {
        return new BotProcessStatistics($statisticsArray['id'], $statisticsArray['likes_count'],
            $statisticsArray['comments_count'], $statisticsArray['follows_count']);
    }

    /**
     * @param BotProcessStatistics $statistics
     * @return array
     */
    private static function statisticsDataToArray(BotProcessStatistics $statistics)
    {
        return ['id' => $statistics->id, 'likes_count' => $statistics->likesCount,
            'comments_count' => $statistics->commentsCount, 'follows_count' => $statistics->followsCount];
    }
}