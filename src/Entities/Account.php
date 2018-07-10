<?php

namespace Entity;

class Account
{
    private $id;
    private $time;
    private $inProcess;
    private $end_time;
    private $limitTime;
    private $dailyPointsCount;
    private $subscriptionEndTime;
    private $target;
    private $commentsLimitTime;
    private $followsLimitTime;

    /**
     * Account constructor.
     * @param $id
     * @param $time
     * @param $subscriptionEndTime
     * @param $target
     * @param null $end_time
     * @param int $limitTime
     * @param null $inProcess
     * @param int $dailyPointsCount
     * @param int $commentsLimitTime
     * @param int $followsLimitTime
     */
    public function __construct($id, $time, $subscriptionEndTime, $target,
                                $end_time = null, $limitTime = 0,
                                $inProcess = null, $dailyPointsCount = 0,
                                $commentsLimitTime = 0, $followsLimitTime = 0){
        $this->id = $id;
        $this->time = $time;
        $this->inProcess = $inProcess;
        $this->end_time = $end_time;
        $this->limitTime = $limitTime;
        $this->dailyPointsCount = $dailyPointsCount;
        $this->subscriptionEndTime = $subscriptionEndTime;
        $this->target = $target;
        $this->commentsLimitTime = $commentsLimitTime;
        $this->followsLimitTime = $followsLimitTime;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getCommentsLimitTime()
    {
        return $this->commentsLimitTime;
    }

    /**
     * @param mixed $commentsLimitTime
     */
    public function setCommentsLimitTime($commentsLimitTime): void
    {
        $this->commentsLimitTime = $commentsLimitTime;
    }

    /**
     * @return mixed
     */
    public function getFollowsLimitTime()
    {
        return $this->followsLimitTime;
    }

    /**
     * @param mixed $followsLimitTime
     */
    public function setFollowsLimitTime($followsLimitTime): void
    {
        $this->followsLimitTime = $followsLimitTime;
    }

    /**
     * @return int
     */
    public function getSubscriptionEndTime(){
        return $this->subscriptionEndTime;
    }

    /**
     * @param mixed $subscriptionEndTime
     */
    public function setSubscriptionEndTime($subscriptionEndTime)
    {
        $this->subscriptionEndTime = $subscriptionEndTime;
    }

    /**
     * @return mixed
     */
    public function getLimitTime()
    {
        return $this->limitTime;
    }

    /**
     * @param mixed $limitTime
     */
    public function setLimitTime($limitTime)
    {
        $this->limitTime = $limitTime;
    }

    /**
     * @return mixed
     */
    public function getDailyPointsCount()
    {
        return $this->dailyPointsCount;
    }

    /**
     * @param mixed $dailyPointsCount
     */
    public function setDailyPointsCount($dailyPointsCount)
    {
        $this->dailyPointsCount = $dailyPointsCount;
    }


    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param mixed $end_time
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function isInProcess()
    {
        return $this->inProcess;
    }

    /**
     * @param $inProcess
     */
    public function setInProcess($inProcess)
    {
        $this->inProcess = $inProcess;
    }

}