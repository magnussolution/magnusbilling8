<?php


namespace app\components;

class MagnusLog
{
    public static function writeLog($fileLog, $log)
    {
        $string_log = "[" . date("d/m/Y H:i:s") . "]:[$log]\n";
        error_log($string_log, 3, Yii::app()->baseUrl . '/' . $fileLog);
        unset($string_log);
    }

    public static function insertLOG($action, $description)
    {
        //
    }
}
