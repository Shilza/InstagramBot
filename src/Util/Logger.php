<?php

namespace Util;

class Logger{
    private static $filePath = '';

    public static function setFilePath($filePath){
        static::$filePath = $filePath;
    }
    
    public static function info($logInfo){
        static::log(" [INFO] --> $logInfo ");
    }

    public static function error($logInfo){
        static::log( " [ERROR] --> $logInfo ");
    }

    public static function trace($logInfo){
        static::log(" [TRACE] --> $logInfo ");
    }

    public static function debug($logInfo){
        static::log(" [DEBUG] --> $logInfo ");
    }

    private static function log($logInfo){
        file_put_contents(
            static::$filePath . ".log",
            PHP_EOL.PHP_EOL.date(DATE_RFC822).$logInfo,
            FILE_APPEND
        );

        static::logToConsole($logInfo . "ID : " . static::$filePath);
    }

    private static function logToConsole($logInfo){
        echo date(DATE_RFC822) . ". $logInfo\n";
    }

}