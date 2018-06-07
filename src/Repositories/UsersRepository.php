<?php

require_once '../Entities/User.php';
require_once '../DatabaseWorker.php';

class UsersRepository extends Repository{

    public static function getUserById($userId)
    {

    }

    public static function addUser(User $user)
    {

    }

    public static function updateUser(User $user)
    {
        $values['login'] = $user->getLogin();
        $values['$password'] = $user->getPassword();
        $values['$registration'] = $user->getRegistration();
        $values['$lastActivity'] = $user->getLastActivity();
        $values['$money'] = $user->getMoney();
        foreach ($user->getSettings() as $key => $value)
            $values[$key] = $value;

        static::update($user->getUserId(), $values);
    }

    protected static function select(array $criterions)
    {
        // TODO: Implement select() method.
    }

    protected static function  update($id, array $values)
    {
        $query = "UPDATE users SET login = :login, password = :password, last_activity = :last_activity,
                      money = :money, following_enabled = :following_enabled,
                      likes_enabled = :likes_enabled, comments_enabled = :comments_enabled WHERE id = :id";
        $values['id'] = $id;

        DatabaseWorker::execute($query, $values);
    }

    protected static function insert(array $values)
    {
        // TODO: Implement insert() method.
    }
}