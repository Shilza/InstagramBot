<?php

require_once 'Repository.php';

class FollowsRepository extends Repository{

    public static function getBy(array $criterions)
    {
        $ownerId = $criterions['owner_id'];
        unset($criterions['owner_id']);
        $criterions = array_filter($criterions);

        if (isset($ownerId) && (is_int($ownerId) || ctype_digit($ownerId))) {
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

    public static function add($follow)
    {
        if($follow instanceof FollowedUser){
            $tableName = 'follows'.$follow->getOwnerId();
            $query = "INSERT INTO $tableName (user_id) VALUES(:user_id)";

            DatabaseWorker::execute($query, ['user_id' => $follow->getUserId()]);
        }
    }

    public static function delete($entity)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $userId
     */
    public static function createTable($userId){
        if (is_int($userId) || ctype_digit($userId)) {
            $query = ("CREATE TABLE follows$userId(
            user_id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
            ) ENGINE = InnoDB");

            DatabaseWorker::execute($query);
        }
    }
}