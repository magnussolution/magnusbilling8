<?php

/**
 * Acoes do modulo "SipTrace".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2025 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\SipTrace;
use app\models\Servers;
use Exception;

class SipTraceController extends CController
{
    private $log_name      = 'resources/reports/siptrace.log';
    public function init()
    {
        $this->instanceModel = new SipTrace;
        $this->abstractModel = SipTrace::find();
        $this->titleReport   = Yii::t('app', 'SipTrace');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id ASC';
        parent::init();
    }

    public function actionDetails()
    {

        try {
            $data = @file_get_contents($this->log_name)
                or die(json_encode([
                    'rows'  => [],
                    'count' => 0,
                    'sum'   => [],
                ]));
        } catch (Exception $e) {
            exit;
        }

        $result = htmlentities($data);

        $modelServers = Servers::find()->query('status = 1')->all();

        $result = explode("U " . date('Y') . "", $result);

        $packet = [];
        $id     = 1;
        $mils   = 0;

        try {
            $callids = json_decode($_GET['callid']);
        } catch (Exception $e) {
            exit('invalid Josn');
        }

        $dateOld = 0;

        foreach ($result as $key => $value) {

            $callid = '';
            $lines  = preg_split('/\r\n|\r|\n/', $value);

            if (count($lines) < 10) {
                continue;
            }

            foreach ($lines as $key => $line) {
                if (preg_match('/Call-ID:/', $line)) {
                    $callid = trim(substr($line, 8));
                } else if (preg_match('/MagnusBilling/', $line)) {
                    $agent = explode(' ', $line);
                    if (isset($agent[2])) {
                        $callids[] = $callid;
                    }
                } else if (preg_match('/To:/', $line)) {
                    $sipto = trim(substr($line, 3));
                }
            }

            if (! in_array($callid, $callids)) {
                continue;
            }
            if ($id > 50) {
                break;
            }

            if (preg_match('/Trying/', $lines[1])) {
                $method = '100 Trying';
            } else if (preg_match('/SIP\/2\.0 /', $lines[1])) {
                $method = explode('SIP/2.0 ', $lines[1]);
                $method = $method[1];
            } else {
                $method = explode(' ', $lines[1]);
                $method = $method[0];
            }

            $fromTo = explode(' ', $lines[0]);

            if (count($fromTo) < 4) {
                continue;
            }

            $date = $fromTo[0] . ' ' . $fromTo[1];

            preg_match_all('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\:.* \-.* (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $value, $output_array);
            $fromIp = $output_array[1][0];
            $toIp   = $output_array[2][0];

            $server_id_from = array_search($fromIp, array_column($modelServers, 'host'));
            $server_id_to   = array_search($toIp, array_column($modelServers, 'host'));

            $time = explode('.', $date);

            $date = date('Y') . $time[0];

            $unixtime = strtotime($date) . '.' . $time[1];

            $mils    = $unixtime - $dateOld;
            $dateOld = $unixtime;
            if ($id == 1) {
                $firstPacket = $fromIp;
                $mils        = $time[1];
            }

            array_push($packet, [
                'id'     => $id,
                'method' => $method,
                'fromip' => $server_id_from !== false ? $modelServers[$server_id_from]->name . ' (' . $fromIp . ')' : $fromIp,
                'toip'   => $server_id_to !== false ? $modelServers[$server_id_to]->name . ' (' . $toIp . ')' : $toIp,
                'from'   => $fromIp,
                'to'     => $toIp,
                'sipto'  => $sipto,
                'callid' => $callid,
                'head'   => date('Y') . preg_replace('/\#/', '', $value),
                'date'   => $id == 1 ? $date : $date . ' + ' . number_format($mils, 3) . 's',
            ]);

            $id++;
        }
        $this->render('index', ['packet' => $packet]);
    }

    public function actionRead($asJson = true, $condition = null)
    {

        $modelServers = Servers::find()->query('status = 1')->all();

        $start = $_GET['start'];

        $filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : [];

        if (count($filter)) {

            foreach ($filter as $key => $value) {
                if ($value->field == 'method') {
                    $filterMethod            = $value->value;
                    $filterMethodComparation = $value->comparison;
                }
                if ($value->field == 'callid') {
                    $filterCallid            = $value->value;
                    $filterCallidComparation = $value->comparison;
                }
                if ($value->field == 'fromip') {
                    $filterFromIp            = $value->value;
                    $filterFromIpComparation = $value->comparison;
                }
                if ($value->field == 'toip') {
                    $filterToIp            = $value->value;
                    $filterToIpComparation = $value->comparison;
                }
                if ($value->field == 'sipto') {
                    $filterSipTo            = $value->value;
                    $filterSipToComparation = $value->comparison;
                }
            }
        }
        try {
            $data = @file_get_contents($this->log_name)
                or die(json_encode([
                    'rows'  => [],
                    'count' => 0,
                    'sum'   => [],
                ]));
        } catch (Exception $e) {
            exit;
        }

        $result = htmlentities($data);

        $result = explode("U " . date('Y') . "", $result);

        $packet = [];
        $id     = 1;
        foreach ($result as $key => $value) {

            $callid = '';

            $lines = preg_split('/\r\n|\r|\n/', $value);

            if (count($lines) < 10) {
                continue;
            }
            if (preg_match('/Trying/', $lines[1])) {
                $method = '100 Trying';
            } else if (preg_match('/SIP\/2\.0 /', $lines[1])) {
                $method = explode('SIP/2.0 ', $lines[1]);
                $method = $method[1];
            } else {
                $method = explode(' ', $lines[1]);
                $method = $method[0];
            }

            $fromTo = explode(' ', $lines[0]);
            if (! isset($fromTo[2])) {
                continue;
            }
            $fromIp = strtok($fromTo[2], ':');

            if (! isset($fromTo[4])) {
                continue;
            }

            $toIp = strtok($fromTo[4], ':');

            foreach ($lines as $key => $line) {
                if (preg_match('/Call-ID:/', $line)) {
                    $callid = trim(substr($line, 8));
                }
                if (preg_match('/To:/', $line)) {
                    $sipto = trim(substr($line, 3));
                }
            }

            if (! isset($sipto)) {
                continue;
            }

            if (isset($filterMethod)) {
                //filter method
                if ($filterMethodComparation == 'st') {
                    if (! preg_match('/^' . $filterMethod . '/', $method)) {
                        continue;
                    }
                }
                if ($filterMethodComparation == 'ed') {
                    if (! preg_match('/' . $filterMethod . '$/', $method)) {
                        continue;
                    }
                }
                if ($filterMethodComparation == 'ct') {
                    if (! preg_match('/' . $filterMethod . '/', $method)) {
                        continue;
                    }
                }
                if ($filterMethodComparation == 'eq') {
                    if ($filterMethod != $method) {
                        continue;
                    }
                }
            }

            if (isset($filterFromIp)) {
                //filter callerid
                if ($filterFromIpComparation == 'st') {
                    if (! preg_match('/^' . $filterFromIp . '/', $fromIp)) {
                        continue;
                    }
                }
                if ($filterFromIpComparation == 'ed') {
                    if (! preg_match('/' . $filterFromIp . '$/', $fromIp)) {
                        continue;
                    }
                }
                if ($filterFromIpComparation == 'ct') {
                    if (! preg_match('/' . $filterFromIp . '/', $fromIp)) {
                        continue;
                    }
                }
                if ($filterFromIpComparation == 'eq') {
                    if ($filterFromIp != $fromIp) {
                        continue;
                    }
                }
            }

            if (isset($filterToIp)) {
                //filter callerid
                if ($filterToIpComparation == 'st') {
                    if (! preg_match('/^' . $filterToIp . '/', $toIp)) {
                        continue;
                    }
                }
                if ($filterToIpComparation == 'ed') {
                    if (! preg_match('/' . $filterToIp . '$/', $toIp)) {
                        continue;
                    }
                }
                if ($filterToIpComparation == 'ct') {
                    if (! preg_match('/' . $filterToIp . '/', $toIp)) {
                        continue;
                    }
                }
                if ($filterToIpComparation == 'eq') {
                    if ($filterToIp != $toIp) {
                        continue;
                    }
                }
            }

            if (isset($filterCallid)) {
                //filter callerid
                if ($filterCallidComparation == 'st') {
                    if (! preg_match('/^' . $filterCallid . '/', $callid)) {
                        continue;
                    }
                }
                if ($filterCallidComparation == 'ed') {
                    if (! preg_match('/' . $filterCallid . '$/', $callid)) {
                        continue;
                    }
                }
                if ($filterCallidComparation == 'ct') {
                    if (! preg_match('/' . $filterCallid . '/', $callid)) {
                        continue;
                    }
                }
                if ($filterCallidComparation == 'eq') {
                    if ($filterCallid != $callid) {
                        continue;
                    }
                }
            }

            if (isset($filterSipTo)) {
                //filter callerid
                if ($filterSipToComparation == 'st') {
                    if (! preg_match('/^' . $filterSipTo . '/', $sipto)) {
                        continue;
                    }
                }
                if ($filterSipToComparation == 'ed') {
                    if (! preg_match('/' . $filterSipTo . '$/', $sipto)) {
                        continue;
                    }
                }
                if ($filterSipToComparation == 'ct') {
                    if (! preg_match('/' . $filterSipTo . '/', $sipto)) {
                        continue;
                    }
                }
                if ($filterSipToComparation == 'eq') {
                    if ($filterSipTo != $sipto) {
                        continue;
                    }
                }
            }

            if ($id < $start) {
                $id++;
                continue;
            }
            if ($id > $start + 25) {
                break;
            }

            $server_id_from = array_search($fromIp, array_column($modelServers, 'host'));
            $server_id_to   = array_search($toIp, array_column($modelServers, 'host'));

            array_push($packet, [
                'id'     => $id,
                'method' => $method,
                'fromip' => $server_id_from !== false ? $modelServers[$server_id_from]->name . ' (' . $fromIp . ')' : $fromIp,
                'toip'   => $server_id_to !== false ? $modelServers[$server_id_to]->name . ' (' . $toIp . ')' : $toIp,
                'sipto'  => $sipto,
                'callid' => $callid,
                'head'   => date('Y') . html_entity_decode($value),
            ]);

            $id++;
        }

        echo json_encode([
            'rows'  => $packet,
            'count' => count($packet) < 25 ? count($packet) : count($result),
            'sum'   => [],
        ], JSON_UNESCAPED_SLASHES);
    }

    public function actionDestroy()
    {
        SipTrace::deleteAll();
        unlink('/var/www/html/mbilling/resources/reports/siptrace.log');
    }

    public function actionExport()
    {
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: inline; filename="MagnusBilling_siptrace_' . time() . '.log"');
        header('Content-Transfer-Encoding: binary');

        header('Accept-Ranges: bytes');
        ob_clean();
        flush();
        readfile($this->log_name);
    }

    public function actionStart()
    {

        $modelTrace = SipTrace::find()->one();

        if (isset($modelTrace->id)) {
            echo json_encode([
                $this->nameSuccess => false,
                $this->nameMsg     => Yii::t('app', 'Exist a filter active or in use. Wait or click in Stop Capture button.'),
            ]);
            exit;
        }
        $modelTrace          = new SipTrace();
        $modelTrace->filter  = $_POST['filter'];
        $modelTrace->timeout = $_POST['timeout'];
        $modelTrace->port    = $_POST['port'];
        $modelTrace->status  = 1;
        $modelTrace->in_use  = 0;
        $modelTrace->save();

        echo json_encode([
            $this->nameSuccess => true,
            $this->nameMsg     => Yii::t('app', 'Reload this module to see the packets'),
        ]);
    }

    public function actionClearAll()
    {
        try {
            SipTrace::deleteAll();
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
