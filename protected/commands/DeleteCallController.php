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

namespace app\commands;

use Yii;
use app\models\Call;
use yii\console\ExitCode;
use app\components\ConsoleCommand;

class DeleteCallController extends ConsoleCommand
{
    public function actionRun($args = '')
    {
        ini_set('memory_limit', '-1');
        $backdate = $this->subDayIntoDate(date('Ymd'), 15);

        Call::deleteAll([
            'and',
            ['sessiontime' => 0],
            ['<', 'starttime', $backdate]
        ]);
        return ExitCode::OK;
    }

    public function subDayIntoDate($date, $days)
    {
        $thisyear  = substr($date, 0, 4);
        $thismonth = substr($date, 4, 2);
        $thisday   = substr($date, 6, 2);
        $nextdate  = mktime(0, 0, 0, $thismonth, $thisday - $days, $thisyear);
        return date("Y-m-d", $nextdate);
    }
}
