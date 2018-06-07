<?php

class FollowedUser{
    private $userId;
    private $date;

    /**
     * FollowedUser constructor.
     * @param $userId
     * @param $date
     */
    public function __construct($userId, $date)
    {
        $this->userId = $userId;
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }


}