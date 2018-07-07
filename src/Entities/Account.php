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

    /**
     * Account constructor.
     * @param $id
     * @param $time
     * @param $inProcess
     * @param $end_time
     * @param $limitTime
     * @param $dailyPointsCount
     * @param $subscriptionEndTime
     */
    public function __construct($id, $time, $subscriptionEndTime, $end_time = null, $limitTime = 0,
                                $inProcess = null, $dailyPointsCount = 0){
        $this->id = $id;
        $this->time = $time;
        $this->inProcess = $inProcess;
        $this->end_time = $end_time;
        $this->limitTime = $limitTime;
        $this->dailyPointsCount = $dailyPointsCount;
        $this->subscriptionEndTime = $subscriptionEndTime;
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