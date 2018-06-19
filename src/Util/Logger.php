<?php
/**
 * Created by PhpStorm.
 * User: Tolek
 * Date: 17.06.2018
 * Time: 21:38
 */

namespace Util;


class Logger{
    private static $filePath = '';

    public static function setFilePath($filePath){
        static::$filePath = $filePath;
    }

    public static function log($logInfo){
        file_put_contents(
            static::$filePath,
            "\n\n".date(DATE_RFC822).': '.$logInfo,
            FILE_APPEND
        );
    }
}