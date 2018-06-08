<?php

class Comment{
    private $id;
    private $ownerId;
    private $mediaId;
    private $text;
    private $date;

    /**
     * Comment constructor.
     * @param $id
     * @param $ownerId
     * @param $mediaId
     * @param $text
     * @param $date
     */
    public function __construct($id, $ownerId, $mediaId, $text, $date = null)
    {
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->mediaId = $mediaId;
        $this->text = $text;
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
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @param mixed $mediaId
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }


}