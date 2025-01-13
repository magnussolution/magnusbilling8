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
use app\components\ConsoleCommand;
use yii\console\ExitCode;

namespace app\commands;

use Yii;
use app\components\ConsoleCommand;
use yii\console\ExitCode;

class AlarmController extends ConsoleCommand
{

    public $filter;
    public function actionRun($args = '')
    {
        $modelAlarm = Alarm::find()->where(['status' => 1])->all();

        foreach ($modelAlarm as $key => $alarm) {

            if ($alarm->period > 30) {
                $this->filter = "starttime  >= '" . date("Y-m-d", time() - $alarm->period) . "'";
            } else {
                $this->filter = "starttime  >= '" . date('Y-m-d', strtotime('-' . $alarm->period . ' day', time())) . "'";
            }
            echo $alarm->type . "\n";
            switch ($alarm->type) {
                case 1:
                    # ALOC
                    $this->aloc($alarm);
                    break;
                case 2:
                    # ASR
                    $this->asr($alarm);
                    break;
                case 3:
                    # CALL PER MIN
                    $this->callPerMin($alarm);
                    break;
                case 4:
                    # CONSECUTIVE NUMBER
                    $this->consecutiveCalls($alarm);
                    break;
                case 5:
                    # ONLINE CALLS ON THE SAME NUMBER
                    $this->onlineCallsSameNumber($alarm);
                    break;
                case 6:
                    # ONLINE CALLS ON THE SAME NUMBER
                    $this->numberEqualCaller($alarm);
                    break;
                case 7:
                    # TOTAL CALLS PER USER
                    $this->totalCallsPerUser($alarm);
                    break;
                case 8:
                    # TOTAL CALLS TRUNK FAIL
                    $this->totalCallsTrunkFail($alarm);
                    break;
            }
        }

        return ExitCode::OK;
    }

    public function asr($alarm)
    {
        $period = time() - $alarm->period;

        $period = date("Y-m-d H:i:s", $period);

        $this->filter = "starttime  > '$period'";

        $sql           = "SELECT count(*) AS sessiontime FROM pkg_cdr WHERE " . $this->filter;
        $modeCdr = Call::findBySql($sql)->one();
        $totalAnswered = $modeCdr->sessiontime;

        $sql         = "SELECT count(*) AS sessiontime FROM pkg_cdr_failed WHERE " . $this->filter;
        $modeCdr     = Call::findBySql($sql)->one();
        $totalFailed = $modeCdr->sessiontime;

        $asr = ($totalAnswered / ($totalFailed + $totalAnswered)) * 100;

        $alarm->message = preg_replace('/%totalAnswered%/', $totalAnswered, $alarm->message);
        $alarm->message = preg_replace('/%totalFailed%/', $totalFailed, $alarm->message);
        $alarm->message = preg_replace('/%asr%/', $asr, $alarm->message);

        echo 'ASR ' . $asr . "\n";
        if ($alarm->condition == 1) {
            if ($asr > $alarm->amount) {
                $this->notification($alarm);
            }
        } else if ($alarm->condition == 2) {
            if ($asr < $alarm->amount) {
                $this->notification($alarm);
            }
        }
    }

    public function aloc($alarm)
    {

        $sql     = "SELECT SUM(sessiontime) / COUNT(*) AS sessiontime FROM pkg_cdr WHERE " . $this->filter;
        $modeCdr = Call::findBySql($sql)->one();
        $aloc    = $modeCdr->sessiontime;

        $alarm->message = preg_replace('/%aloc%/', $aloc, $alarm->message);

        echo 'ALOC ' . $aloc . "\n";
        if ($alarm->condition == 1) {
            if ($aloc > $alarm->amount) {
                $this->notification($alarm->message);
            }
        } else if ($alarm->condition == 2) {
            if ($aloc < $alarm->amount) {
                $this->notification($alarm->message);
            }
        }
    }

    public function callPerMin($alarm)
    {

        $sql        = "SELECT  COUNT(*) AS sessiontime FROM pkg_cdr WHERE " . $this->filter;
        $modeCdr    = Call::findBySql($sql);
        $totalCalls = $modeCdr->sessiontime;

        $minutes = ($alarm->period / 3600) * 60;

        $callPerMin = $totalCalls / $minutes;

        $alarm->message = preg_replace('/%callPerMin%/', $callPerMin, $alarm->message);
        $alarm->message = preg_replace('/%totalCalls%/', $totalCalls, $alarm->message);

        echo 'CALLS PER MINUTE ' . $callPerMin . "\n";
        if ($alarm->condition == 1) {
            if ($callPerMin > $alarm->amount) {
                $this->notification($alarm);
            }
        } else if ($alarm->condition == 2) {
            if ($callPerMin < $alarm->amount) {
                $this->notification($alarm);
            }
        }
    }

    public function consecutiveCalls($alarm)
    {

        $sql     = "SELECT  *, COUNT(*) AS sessiontime FROM pkg_cdr WHERE " . $this->filter . " AND sipiax = 0 GROUP BY calledstation, id_user ORDER BY sessiontime DESC";
        $modeCdr = Call::findBySql($sql)->all();

        foreach ($modeCdr as $key => $cdr) {

            $totalConsecutiveCalls = $cdr->sessiontime;

            $alarm->message = preg_replace('/%totalConsecutiveCalls%/', $totalConsecutiveCalls, $alarm->message);

            foreach ($cdr->idUser as $key => $value) {
                $alarm->message = preg_replace('/%' . $key . '%/', $cdr->idUser->$key, $alarm->message);
            }

            if ($alarm->condition == 1) {
                if ($totalConsecutiveCalls > $alarm->amount) {
                    $this->notification($alarm);
                }
            } else if ($alarm->condition == 2) {
                if ($totalConsecutiveCalls < $alarm->amount) {
                    $this->notification($alarm);
                }
            }
        }
    }

    public function onlineCallsSameNumber($alarm)
    {

        $modelCallOnLine = CallOnLine::find()
            ->select(['*', 'ndiscado', 'COUNT(*) AS canal'])
            ->groupBy('ndiscado')
            ->having(['>=', 'canal', $alarm->amount])
            ->orderBy(['canal' => SORT_DESC])
            ->all();
        foreach ($modelCallOnLine as $key => $call) {

            if (($call->canal) >= ($alarm->amount)) {

                print_r($call->getAttributes());
                $alarm->message = preg_replace('/%onlineCalls%/', $call->canal, $alarm->message);
                $alarm->message = preg_replace('/%number%/', $call->ndiscado, $alarm->message);

                echo $alarm->message . "\n\n";
                foreach ($call->idUser as $key => $value) {
                    $alarm->message = preg_replace('/%' . $key . '%/', $call->idUser->$key, $alarm->message);
                }
                $this->notification($alarm);
            }
        }
    }
    public function numberEqualCaller($alarm)
    {

        $sql     = "SELECT *, COUNT(*) id, calledstation FROM pkg_cdr WHERE " . $this->filter . " AND (calledstation = callerid  OR SUBSTRING(calledstation,2) = callerid)";
        $modeCdr = Call::findBySql($sql)->one();

        if (($modeCdr->id) >= ($alarm->amount)) {
            $alarm->message = preg_replace('/%number%/', $modeCdr->calledstation, $alarm->message);
            $alarm->message = preg_replace('/%totalCalls%/', $modeCdr->id, $alarm->message);

            $this->notification($alarm);
        }
    }

    public function totalCallsPerUser($alarm)
    {
        if ($alarm->period < 1000 && $alarm->last_notification > date('Y-m-d')) {
            //interval more than 1 days, only send notification email 1 time per day
            return;
        }
        $modelUser = User::find()->where(['>', 'id', 1])->andWhere(['active' => 1])->andWhere(['<', 'id_user', 2])->all();

        $users = "username,name,credit,calls,lastuse,info<br>";
        foreach ($modelUser as $key => $user) {

            if ($alarm->period > 1000) {
                $sql     = "SELECT count(id) AS id FROM pkg_cdr WHERE id_user = " . $user->id . " AND " . $this->filter;
                $modeCdr = Call::findBySql($sql)->one();
            } else {
                $sql     = "SELECT sum(nbcall) AS id FROM pkg_cdr_summary_day_user WHERE id_user = " . $user->id . " AND " . preg_replace('/starttime/', 'day', $this->filter);
                $modeCdr = CallSummaryDayUser::findBySql($sql)->one();
            }

            $calls = is_numeric($modeCdr->id) ? $modeCdr->id : 0;

            if ($alarm->condition == 1) {
                if ($modeCdr->id > $alarm->amount) {
                    $modeCdr2 = Call::find()
                        ->where(['id_user' => $user->id])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();
                    $lastcall = isset($modeCdr2->starttime) ? $modeCdr2->starttime : 0;
                    $users .= $user->username . ',' . $user->lastname . ' ' . $user->firstname . ',' . $user->credit . "," . $calls . "," . $lastcall . ",bigger than alarme configuration<br>";
                }
            } else if ($alarm->condition == 2) {
                if ($modeCdr->id < $alarm->amount) {
                    $modeCdr2 = Call::find()
                        ->where(['id_user' => $user->id])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();
                    $lastcall = isset($modeCdr2->starttime) ? $modeCdr2->starttime : 0;
                    $users .= $user->username . ',' . $user->lastname . ' ' . $user->firstname . ',' . $user->credit . "," . $calls . "," . $lastcall . ",less than alarme configuration<br>";
                }
            }
        }

        if (strlen($users) > 3) {
            $alarm->message = preg_replace('/%userList%/', $users, $alarm->message);
            $this->notification($alarm);
        }
    }

    public function totalCallsTrunkFail($alarm)
    {
        if ($alarm->period < 1000 && $alarm->last_notification > date('Y-m-d')) {
            //interval more than 1 days, only send notification email 1 time per day
            return;
        }

        $period = time() - $alarm->period;

        $period = date("Y-m-d H:i:s", $period);

        $this->filter = "starttime  > '$period'";

        $modelTrunk = Trunk::find()->where(['status' => 1])->all();

        foreach ($modelTrunk as $key => $trunk) {

            $sql     = "SELECT count(id) AS id FROM pkg_cdr_failed WHERE id_trunk = " . $trunk->id . " AND " . $this->filter;
            $modeCdr = Call::findBySql($sql)->one();

            $calls = is_numeric($modeCdr->id) ? $modeCdr->id : 0;

            echo $calls;

            if ($alarm->condition == 1) {
                if ($modeCdr->id > $alarm->amount) {
                    $alarm->message = preg_replace('/%trunk%/', $trunk->trunkcode, $alarm->message);
                    $alarm->message = preg_replace('/%totalCalls%/', $modeCdr->id, $alarm->message);
                    $this->notification($alarm);
                }
            } else if ($alarm->condition == 2) {
                if ($modeCdr->id < $alarm->amount) {
                    $this->notification($alarm);
                }
            }
        }
    }

    public function notification($alarm)
    {
        $condition = [
            1 => 'Bigger than',
            2 => 'Less than',
        ];

        $type = [
            1 => 'ALOC',
            2 => 'ASR',
            3 => 'Calls per minute',
            4 => 'Consecutive number',
            5 => 'Online calls on same number',
            6 => 'Same number and CallerID',
            7 => 'Total calls per user',
            8 => 'Failed calls per trunk',
        ];

        $period = [
            '3600'  => '1 Hour',
            '7200'  => '2 Hours',
            '43200' => '12 Hours',
            '1'     => '1 day',
            '2'     => '2 days',
            '3'     => '3 days',
            '4'     => '4 days',
            '5'     => '5 days',
            '6'     => '6 days',
            '7'     => '1 week',
            '14'    => '2 weeks',
            '21'    => '3 weeks',
            '30'    => '1 month',
        ];

        $sql = "UPDATE pkg_alarm SET last_notification = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $alarm->id;
        Yii::$app->db->createCommand($sql)->execute();

        foreach ($alarm as $key => $value) {
            if ($key == 'type') {
                $alarm->message = preg_replace('/%' . $key . '%/', $type[$alarm->$key], $alarm->message);
            } else if ($key == 'condition') {
                $alarm->message = preg_replace('/%' . $key . '%/', $condition[$alarm->$key], $alarm->message);
            } else if ($key == 'period') {
                $alarm->message = preg_replace('/%' . $key . '%/', $period[$alarm->$key], $alarm->message);
            } else {
                $alarm->message = preg_replace('/%' . $key . '%/', $alarm->$key, $alarm->message);
            }
        }

        echo $alarm->message . "\n";

        $modelSmtps = Smtps::find()->where(['id_user' => 1])->one();

        if (! isset($modelSmtps->id)) {
            return;
        }
        $smtp_host       = $modelSmtps->host;
        $smtp_encryption = $modelSmtps->encryption;
        $smtp_username   = $modelSmtps->username;
        $smtp_password   = $modelSmtps->password;
        $smtp_port       = $modelSmtps->port;

        if ($smtp_encryption == 'null') {
            $smtp_encryption = '';
        }

        if ($smtp_host == 'mail.magnusbilling.com' || $smtp_host == '' || $smtp_username == '' || $smtp_password == '' || $smtp_port == '') {
            return;
        }

        $modelTemplate = TemplateMail::find()
            ->where(['!=', 'fromemail', 'noreply@site.com'])
            ->one();
        $from_email = isset($modelTemplate->fromemail) ? $modelTemplate->fromemail : $smtp_username;

        $mailServer = [
            'mail' => [
                'class' => 'yii\swiftmailer\Mailer',
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => $smtp_host,
                    'username' => $smtp_username,
                    'password' => $smtp_password,
                    'port' => $smtp_port,
                    'encryption' => $smtp_encryption,
                ],
            ]
        ];

        //$mailServer['mail']['viewPath'] = '@app/mail';
        //$mailServer['mail']['useFileTransport'] = false;

        Yii::$app->setComponents($mailServer);

        $message = Yii::$app->mail->compose();
        if ($smtp_host == 'smtp.office365.com' || $this->from_email == 'noreply@site.com') {
            $message->SetFrom([$smtp_username => 'Billing Alert']);
        } else {
            $message->SetFrom([$from_email => 'Billing Alert']);
        }


        $message->setSubject(mb_encode_mimeheader($alarm->subject));
        $message->setHtmlBody($alarm->message);
        $to = [$alarm->email];

        $message->setTo($to);

        $message->send();
    }
}
