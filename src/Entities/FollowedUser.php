<?php

class FollowedUser{
    private $userId;
    private $ownerId;
    private $date;

    /**
     * FollowedUser constructor.
     * @param $userId
     * @param $ownerId
     * @param $date
     */
    public function __construct($userId, $ownerId, $date = null)
    {
        $this->userId = $userId;
        $this->ownerId = $ownerId;
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param mixed $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
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