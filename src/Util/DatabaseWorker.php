<?php

namespace Util;

class DatabaseWorker{
    private static $db;

    private static function getDBDatas(){
        return explode(" ", file_get_contents("configDB", FILE_USE_INCLUDE_PATH));
    }

    private static function connect(){
        static::$db = new \PDO('mysql:host=localhost;dbname=InstaTest', "root", "",
            [\PDO::ATTR_PERSISTENT => true]);
        //$arr = $this->getDBDatas();
        //$this->db = new \PDO("mysql:host=".$arr[0]."dbname=".$arr[1], $arr[2], $arr[3]);
    }

    private static function disconnect(){
        static::$db = null;
    }

    public static function execute($queryString, array $values = null){
        static::connect();

        $query = static::$db->prepare($queryString);

        if (isset($values))
            foreach (array_keys($values) as $key)
                $query->bindParam(':'.$key, $values[$key]);

        $query->execute();
        static::disconnect();

        return $query->fetchAll();
    }
}