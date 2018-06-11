<?php

class Account
{
    private $id;
    private $time;
    private $inProcess;

    /**
     * Account constructor.
     * @param $id
     * @param $time
     * @param $inProcess
     */
    public function __construct($id, $time, $inProcess = null)
    {
        $this->id = $id;
        $this->time = $time;
        $this->inProcess = $inProcess;
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