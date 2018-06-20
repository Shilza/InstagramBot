<?php

namespace Entity;

class Account
{
    private $id;
    private $time;
    private $inProcess;
    private $end_time;

    /**
     * Account constructor.
     * @param $id
     * @param $time
     * @param null $inProcess
     * @param null $end_time
     */
    public function __construct($id, $time, $inProcess = null, $end_time = null)
    {
        $this->id = $id;
        $this->time = $time;
        $this->inProcess = $inProcess;
        $this->end_time = $end_time;
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