<?php

namespace Repository;

use Entity\User;
use Util\DatabaseWorker;

class UsersRepository extends Repository implements Updatable{

    /**
     * @param array $criterions
     * @return array User|null
     */
    public static function getBy(array $criterions)
    {
        if(count($criterions) > 0) {
            $query = "SELECT * FROM users WHERE ";
            foreach ($criterions as $key => $value)
                $query .= "$key='$value' AND ";

            $usersArray = DatabaseWorker::execute(substr($query, 0, iconv_strlen($query) - 5));
            $userObjectsArray = [];

            foreach ($usersArray as $user)
                array_push($userObjectsArray, static::dataArrayToUser($user));

            return $userObjectsArray;
        }
        else
            return null;
    }

    /**
     * @param User $user
     */
    public static function update($user)
    {
        if ($user instanceof User) {
            $query = "UPDATE users SET login = :login, password = :password, last_activity = :last_activity,
                      money = :money, following_selected = :following_selected,
                      likes_selected = :likes_selected, comments_selected = :comments_selected,
                      genesis_account_bot_selected = :genesis_account_bot_selected,
                      hashtag_bot_selected = :hashtag_bot_selected,
                      geotag_bot_selected = :geotag_bot_selected
                      WHERE id = :id";

            DatabaseWorker::execute($query, static::usersDataToArray($user));
        }
    }

    /**
     * @param $user
     * @return mixed|void
     */
    public static function add($user)
    {
        if ($user instanceof User && static::isValid($user->getUserId())) {
            $query = /** @lang SQL */
                "INSERT INTO users (id, login, password, last_activity,
                      money, following_selected, likes_selected, comments_selected,
                      genesis_account_bot_selected, hashtag_bot_selected, geotag_bot_selected) 
                      VALUES(:id, :login, :password, :last_activity,
                      :money, :following_selected, :likes_selected, :comments_selected,
                      :genesis_account_bot_selected, :hashtag_bot_selected, :geotag_bot_selected)";

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
     * @return mixed
     */
    private static function usersDataToArray(User $user){
        $values['id'] = $user->getUserId();
        $values['login'] = $user->getLogin();
        $values['password'] = $user->getPassword();
        $values['last_activity'] = $user->getLastActivity();
        $values['money'] = $user->getMoney();
        foreach ($user->getSettings() as $key => $value)
            $values[$key] = $value;

        return $values;
    }

    private static function dataArrayToUser(array $userData){
        return new User(
            $userData['id'], $userData['login'], $userData['password'],
            $userData['registration'], $userData['last_activity'], $userData['money'],
            [
                'following_selected' => $userData['following_selected'],
                'likes_selected' => $userData['likes_selected'],
                'comments_selected' => $userData['comments_selected'],
                'genesis_account_bot_selected' => $userData['genesis_account_bot_selected'],
                'hashtag_bot_selected' => $userData['hashtag_bot_selected'],
                'geotag_bot_selected' => $userData['geotag_bot_selected']
            ]
        );
    }
}