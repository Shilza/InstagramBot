<?php

namespace Util;

class Logger{
    private static $filePath = '';

    public static function setFilePath($filePath){
        static::$filePath = $filePath;
    }

    public static function log($logInfo){
        file_put_contents(
            static::$filePath,
            PHP_EOL.PHP_EOL.date(DATE_RFC822).': '.$logInfo,
            FILE_APPEND
        );
    }
}