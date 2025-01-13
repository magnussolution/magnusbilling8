<?php

/**
 * Classe de com funcionalidades globais
 *
 * MagnusBilling <info@magnusbilling.com>
 * 08/07/2017
 */

namespace app\components;

use Yii;

class LinuxAccess
{

    public static function exec($command)
    {

        Yii::error('LinuxAccess::exec -> ' . $command, 'error');
        exec($command, $output);
        return $output;
    }

    public static function getDirectoryDiskSpaceUsed($filter = '*', $directory = '/var/spool/asterisk/monitor/')
    {
        $command = 'ls -lR  ' . $directory . $filter . ' | grep -v \'^d\' | awk \'{total += $5} END {print total}\'';
        return @LinuxAccess::exec($command);
    }

    public static function getLastFileInDirectory($filter = '*', $directory = '/var/spool/asterisk/monitor/')
    {
        $command = 'ls -tr ' . $directory . $filter . ' | head -n 1';
        return @LinuxAccess::exec($command);
    }
}
