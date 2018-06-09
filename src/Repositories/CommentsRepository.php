<?php

require_once 'Updatable.php';
require_once 'Repository.php';

class CommentsRepository extends Repository implements Updatable {

    /**
     * @param array $criterions
     * @return array|null
     */
    public static function getBy(array $criterions)
    {
        $ownerId = $criterions['owner_id'];
        unset($criterions['owner_id']);
        $criterions = array_filter($criterions);

        if (static::isValid($ownerId)) {
            $query = "SELECT * FROM comments$ownerId WHERE ";

            if(count($criterions) == 0)
                str_replace(" WHERE ", "", $query);
            else
                foreach ($criterions as $key => $value)
                    $query .= "$key='$value' AND ";

            $commentsArray = DatabaseWorker::execute(substr($query, 0, iconv_strlen($query) - 5));
            $commentObjectsArray = [];

            foreach ($commentsArray as $comment) {
                $comment['owner_id'] = $ownerId;
                array_push($commentObjectsArray, static::dataArrayToComment($comment));
            }

            return $commentObjectsArray;
        } else
            return null;
    }

    /**
     * @param Comment $comment
     */
    public static function update($comment)
    {
        $ownerId = $comment->getOwnerId();
        if($comment instanceof Comment && static::isValid($ownerId)){
            $query = "UPDATE comments$ownerId SET id = :id, media_id = :media_id, text = :text";

            DatabaseWorker::execute($query, static::commentsDataToArray($comment));
        }
    }

    /**
     * @param Comment $comment
     */
    public static function add($comment)
    {
        $ownerId = $comment->getOwnerId();
        if($comment instanceof Comment && static::isValid($ownerId)){
            $query = "INSERT INTO comments$ownerId (id, media_id, text) 
                      VALUES(:id, :media_id, :text)";

            DatabaseWorker::execute($query, static::commentsDataToArray($comment));
        }
    }

    /**
     * @param Comment $comment
     */
    public static function delete($comment)
    {
        $ownerId = $comment->getOwnerId();
        if($comment instanceof Comment && static::isValid($ownerId)){
            $query = "DELETE FROM comments$ownerId WHERE id=:id";

            DatabaseWorker::execute($query, ['id' => $comment->getId()]);
        }
    }

    /**
     * @param $userId
     */
    public static function createTable($userId)
    {
        if (static::isValid($userId)) {
            $query = ("CREATE TABLE comments$userId(
            id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            media_id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            text VARCHAR(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
           ) ENGINE = InnoDB");

            DatabaseWorker::execute($query);
        }
    }

    /**
     * @param Comment $comment
     * @return array
     */
    private static function commentsDataToArray(Comment $comment){
        return ['id' => $comment->getId(), 'media_id' => $comment->getMediaId(),
                'text' => $comment->getText()];
    }

    /**
     * @param array $commentData
     * @return Comment
     */
    private static function dataArrayToComment(array $commentData){
        return new Comment($commentData['id'], $commentData['owner_id'],
            $commentData['media_id'], $commentData['text'], $commentData['date']);
    }
}