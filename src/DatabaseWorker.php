<?php

class DatabaseWorker{
    private static $instance;
    private $db;

    private function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=InstaTest', "root", "");
    }

    private static function getInstance(){
        if(!isset(static::$instance))
            static::$instance = new DatabaseWorker();

        return static::$instance;
    }

    public static function execute($queryString, array $values = null)
    {
        $query = static::getInstance()->db->prepare($queryString);

        if (isset($values))
            foreach (array_keys($values) as $key)
                $query->bindParam(':'.$key, $values[$key]);

        $query->execute();
        return $query->fetchAll();
    }
}