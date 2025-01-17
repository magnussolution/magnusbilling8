<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2023 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

namespace app\components;

use Yii;
use app\components\LoadConfig;
use app\components\MagnusLog;
use yii\console\Controller;
use yii\console\ExitCode;

class ConsoleCommand extends Controller
{
    public $debug = 0;
    public $config;
    public function init()
    {

        // Get part of the string before the first occurrence of a specific character
        $routeParts = explode('/', Yii::$app->requestedRoute);
        $className = $routeParts[0];


        $this->config        = LoadConfig::getConfig();
        Yii::$app->language = Yii::$app->sourceLanguage = isset($this->config['global']['base_language'])
            ? $this->config['global']['base_language']
            : Yii::$app->language;

        define('LOGFILE', 'protected/runtime/' . $className . '.log');

        if (! defined('PID')) {
            define("PID", '/var/run/magnus/' . $className . 'Pid.php');
        }

        if (isset($_SERVER['argv'][2])) {
            if ($_SERVER['argv'][2] == 'log') {
                $this->debug = 1;
            } elseif ($_SERVER['argv'][2] == 'logAll') {
                $this->debug = 2;
            }
        }
        if ($this->debug > 0) {
            Process::activate();
        } else {
            if (Process::isActive()) {
                $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " PROCESS IS ACTIVE ") : null;
                die();
            } else {
                Process::activate();
            }
        }

        $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " START " . strtoupper($this->getName()) . " COMMAND ") : null;

        parent::init();
    }
}
