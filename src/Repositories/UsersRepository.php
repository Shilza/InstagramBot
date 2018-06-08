<?php

require_once 'src/Entities/User.php';
require_once 'src/DatabaseWorker.php';
require_once 'Repository.php';
require_once 'Updatable.php';

class UsersRepository extends Repository implements Updatable{

    /**
     * @param array $criterions
     * @return array|null
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
                      money = :money, following_enabled = :following_enabled,
                      likes_enabled = :likes_enabled, comments_enabled = :comments_enabled WHERE id = :id";

            DatabaseWorker::execute($query, static::usersDataToArray($user));
        }
    }

    /**
     * @param User $values
     */
    public static function add($user)
    {
        if ($user instanceof User) {
            $query = "INSERT INTO users (id, login, password, last_activity,
                      money, following_enabled, likes_enabled, comments_enabled) 
                      VALUES(:id, :login, :password, :last_activity,
                      :money, :following_enabled, :likes_enabled, :comments_enabled)";

            DatabaseWorker::execute($query, static::usersDataToArray($user));
        }
    }

    public static function delete($entity)
    {
        // TODO: Implement delete() method.
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
                'following_enabled' => $userData['following_enabled'],
                'likes_enabled' => $userData['likes_enabled'],
                'comments_enabled' => $userData['comments_enabled']
            ]
        );
    }
}