<?php

namespace Repository;
use Entity\FollowedUser;
use Util\DatabaseWorker;

class FollowsRepository extends Repository{

    /**
     * @param array $criterions
     * @return FollowedUser[]|null
     */
    public static function getBy(array $criterions)
    {
        $ownerId = $criterions['owner_id'];
        unset($criterions['owner_id']);
        $criterions = array_filter($criterions);

        if (static::isValid($ownerId)) {
            $query = "SELECT * FROM follows$ownerId WHERE ";

            if(count($criterions) == 0)
                str_replace(" WHERE ", "", $query);
            else
                foreach ($criterions as $key => $value)
                    $query .= "$key='$value' AND ";

            $followsArray = DatabaseWorker::execute(substr($query, 0, iconv_strlen($query) - 5));
            $followObjectsArray = [];

            foreach ($followsArray as $follow)
                array_push($followObjectsArray,
                    new FollowedUser($follow['user_id'], $ownerId, $follow['date']));

            return $followObjectsArray;
        } else
            return null;
    }

    /**
     * @param FollowedUser $follow
     */
    public static function add($follow)
    {
        $ownerId = $follow->getOwnerId();
        if($follow instanceof FollowedUser && static::isValid($ownerId)){
            $query = "INSERT INTO follows$ownerId (user_id) VALUES(:user_id)";

            DatabaseWorker::execute($query, ['user_id' => $follow->getUserId()]);
        }
    }

    /**
     * @param FollowedUser $follow
     */
    public static function delete($follow)
    {
        $ownerId = $follow->getOwnerId();
        if($follow instanceof FollowedUser && static::isValid($ownerId)){
            $query = "DELETE FROM follows$ownerId WHERE user_id=:user_id";

            DatabaseWorker::execute($query, ['user_id' => $follow->getUserId()]);
        }
    }

    /**
     * @param $userId
     */
    public static function createTable($userId){
        if (static::isValid($userId)) {
            $query = ("CREATE TABLE follows$userId(
            user_id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
            ) ENGINE = InnoDB");

            DatabaseWorker::execute($query);
        }
    }
}