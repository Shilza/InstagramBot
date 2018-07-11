<?php

namespace Entity;

use Repository\AccountsRepository;

class Account
{
    private $id;
    private $time;
    private $inProcess;
    private $end_time;
    private $limitTime;
    private $dailyPointsCount;
    private $target;
    //for identification in db
    private $oldTarget;

    /**
     * Account constructor.
     * @param $id
     * @param $target
     * @param int $end_time
     * @param int $time
     * @param int $limitTime
     * @param null $inProcess
     * @param int $dailyPointsCount
     */
    public function __construct($id, $target, $end_time = -1, $time = 0, $limitTime = 0,
                                $inProcess = null, $dailyPointsCount = 0){
        $this->id = $id;
        $this->time = $time;
        $this->inProcess = $inProcess;
        $this->limitTime = $limitTime;
        $this->dailyPointsCount = $dailyPointsCount;
        $this->target = $target;
        $this->oldTarget = $target;

        if($end_time < 0 && $target == 1)
            $this->end_time = time() + AccountsRepository::DAY;
        else
            $this->end_time = $end_time;
    }

    /**
     * @return mixed
     */
    public function getOldTarget()
    {
        return $this->oldTarget;
    }

    /**
     * @param mixed $oldTarget
     */
    public function setOldTarget($oldTarget): void
    {
        $this->oldTarget = $oldTarget;
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