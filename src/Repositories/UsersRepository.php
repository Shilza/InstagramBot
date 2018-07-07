<?php

namespace Repository;

use Entity\User;
use Util\DatabaseWorker;

class UsersRepository extends Repository implements Updatable
{

    /**
     * @param array $criterions
     * @return array User|null
     */
    public static function getBy(array $criterions)
    {
        if (count($criterions) > 0) {
            $query = "SELECT users.*, user_settings.* 
              FROM users JOIN user_settings ON user_settings.id = users.id WHERE ";
            foreach ($criterions as $key => $value)
                $query .= "users.$key='$value' AND ";

            $usersArray = DatabaseWorker::execute(substr($query, 0, iconv_strlen($query) - 5));
            $userObjectsArray = [];

            foreach ($usersArray as $user)
                array_push($userObjectsArray, static::dataArrayToUser($user));

            return $userObjectsArray;
        } else
            return null;
    }

    /**
     * @param User $user
     */
    public static function update($user)
    {
        if ($user instanceof User) {
            $query = "UPDATE users, user_settings
            SET users.login = :login, users.password = :password, 
             users.last_activity = :last_activity,
             users.money = :money, user_settings.followings = :followings,
             user_settings.likes = :likes, user_settings.comments = :comments,
             user_settings.genesis_account_bot = :genesis_account_bot,
             user_settings.hashtag_bot = :hashtag_bot,
             user_settings.geotag_bot = :geotag_bot,
             user_settings.standard_geotags = :standard_geotags,
             user_settings.standard_hashtags = :standard_hashtags,
             user_settings.standard_comments = :standard_comments,
             user_settings.hashtags = :hashtags,
             user_settings.geotags = :geotags,  
             user_settings.custom_comments = :custom_comments
            WHERE users.id = :id AND user_settings.id = :id";

            DatabaseWorker::execute($query, static::usersDataToArray($user));
        }
    }

    /**
     * @param $user
     */
    public static function add($user)
    {
        if ($user instanceof User && static::isValid($user->getUserId())) {
            $query = /** @lang SQL */
                "INSERT INTO users (id, login, password, last_activity) 
                      VALUES(:id, :login, :password, :last_activity)";

            DatabaseWorker::execute($query, static::usersDataToArray($user));
        }
    }

    /**
     * @param User $user
     */
    public static function delete($user)
    {
        if ($user instanceof User && static::isValid($user->getUserId())) {
            $query = "DELETE FROM users WHERE id=:id";

            DatabaseWorker::execute($query, ['id' => $user->getUserId()]);
        }
    }

    /**
     * @param User $user
     * @return array
     */
    private static function usersDataToArray(User $user)
    {
        $values['id'] = $user->getUserId();
        $values['login'] = $user->getLogin();
        $values['password'] = $user->getPassword();
        $values['last_activity'] = $user->getLastActivity();

        if (!is_null($user->getMoney()))
            $values['money'] = $user->getMoney();

        if (!is_null($user->getSettings()))
            foreach ($user->getSettings() as $key => $value)
                $values[$key] = $value;

        return $values;
    }

    /**
     * @param array $userData
     * @return User
     */
    private static function dataArrayToUser(array $userData)
    {
        return new User(
            $userData['id'], $userData['login'], $userData['password'],
            $userData['last_activity'], $userData['registration'], $userData['money'],
            [
                'followings' => $userData['followings'],
                'likes' => $userData['likes'],
                'comments' => $userData['comments'],
                'genesis_account_bot' => $userData['genesis_account_bot'],
                'hashtag_bot' => $userData['hashtag_bot'],
                'geotag_bot' => $userData['geotag_bot'],
                'standard_hashtags' => $userData['standard_hashtags'],
                'standard_geotags' => $userData['standard_geotags'],
                'standard_comments' => $userData['standard_comments'],
                'hashtags' => explode(" ", $userData['hashtags']),
                'geotags' => explode(" ", $userData['geotags']),
                'custom_comments' => explode(" ", $userData['custom_comments'])
            ]
        );
    }
}