<?php

/**
 * Acoes do modulo "CallOnLine".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\CallOnLine;
use app\models\PhoneNumber;
use app\models\Servers;
use app\models\Sip;
use app\components\AsteriskAccess;
use app\components\AccessManager;

class CallOnLineController extends CController
{
    public $extraValues    = ['idUser' => 'username,credit'];

    public $fieldsInvisibleClient = [
        'tronco',
    ];

    public $fieldsInvisibleAgent = [
        'tronco',
    ];

    public function init()
    {
        $this->instanceModel = new CallOnLine;
        $this->abstractModel = CallOnLine::find();
        $this->titleReport   = Yii::t('app', 'Calls Online');
        $this->attributeOrder = $this->instanceModel::tableName() . '.duration DESC, ' . $this->instanceModel::tableName() . '.status ASC';
        parent::init();

        if (Yii::$app->getSession()->get('isAgent')) {
            $this->filterByUser        = true;
            $this->defaultFilterByUser = 'b.id_user';
            $this->join                = 'JOIN pkg_user b ON t.id_user = b.id';
        }
    }

    public function actionRead($asJson = true, $condition = null)
    {

        //altera o sort se for a coluna idUsercredit.
        if (isset($_GET['sort']) && $_GET['sort'] === 'idUsercredit') {
            $_GET['sort'] = '';
        }
        return parent::actionRead($asJson = true, $condition = null);
    }

    public function actionGetChannelDetails()
    {
        $channel = AsteriskAccess::getCoreShowChannel($_POST['channel'], null, $_POST['server']);

        $sipcallid = explode("\n", $channel['SIPCALLID']['data']);

        foreach ($sipcallid as $key => $line) {
            if (preg_match("/Received Address/", $line)) {
                $from_ip = explode(" ", $line);
                $from_ip = end($from_ip);
            }
            if (preg_match("/Audio IP/", $line)) {

                $reinvite = explode(" ", $line);
                $reinvite = end($reinvite);
            }
        }

        if (preg_match('/^MC\!/', $channel['accountcode'])) {

            $modelPhonenumber = PhoneNumber::find()->where(['number' => $channel['Caller ID']])->one();

            echo json_encode([
                'success'     => true,
                'msg'         => 'success',
                'description' => Yii::$app->session['isAdmin'] ? print_r($channel, true) : '',
                'codec'       => $channel['WriteFormat'],
                'billsec'     => $channel['billsec'],
                'callerid'    => $modelPhonenumber->name . ' ' . $modelPhonenumber->city,
                'from_ip'     => $from_ip,
                'reinvite'    => preg_match("/local/", $reinvite) ? 'no' : 'yes',
                'ndiscado'    => $channel['Caller ID'],
            ]);
        } else {
            echo json_encode([
                'success'     => true,
                'msg'         => 'success',
                'description' => Yii::$app->session['isAdmin'] ? print_r($channel, true) : '',
                'codec'       => $channel['WriteFormat'],
                'billsec'     => $channel['billsec'],
                'callerid'    => $channel['Caller ID'],
                'from_ip'     => $from_ip,
                'reinvite'    => preg_match("/local/", $reinvite) ? 'no' : 'yes',
                'ndiscado'    => $channel['dnid'],
            ]);
        }
    }

    public function actionDestroy()
    {
        if (! AccessManager::getInstance($this->instanceModel->getModule())->canDelete()) {
            header('HTTP/1.0 401 Unauthorized');
            die("Access denied to delete in module:" . $this->instanceModel->getModule());
        }

        # recebe os parametros da exclusao
        $values       = $this->getAttributesRequest();
        $namePk       = $this->abstractModel->primaryKey();
        $arrayPkAlias = explode('.', $this->abstractModel->primaryKey());
        $ids          = [];

        foreach ($values as $key => $channel) {

            $modelChannel = $this->abstractModel->query('canal = :key', [':key' => $channel['channel']])->one();
            if (isset($modelChannel->canal)) {
                AsteriskAccess::instance()->hangupRequest($modelChannel->canal, $modelChannel->server);
            }
        }

        # retorna o resultado da execucao
        echo json_encode([
            $this->nameSuccess => true,
            $this->nameMsg     => $this->success,
        ]);
    }

    public function actionSpyCall()
    {
        if (isset($_POST['sipuser'])) {
            $dialstr = 'SIP/' . $_POST['sipuser'];
        } elseif (! isset($_POST['id_sip'])) {
            $dialstr = 'SIP/' . $this->config['global']['channel_spy'];
        } else {
            $modelSip = Sip::findOne((int) $_POST['id_sip']);
            $dialstr  = 'SIP/' . $modelSip->name;
        }

        $call = "Action: Originate\n";
        $call .= "Channel: " . $dialstr . "\n";
        $call .= "Context: billing\n";
        $call .= "Extension: 5555\n";
        $call .= "Priority: 1\n";
        $call .= "Set:SPY=1\n";
        $call .= "Set:SPYTYPE=" . $_POST['type'] . "\n";
        $call .= "Set:CHANNELSPY=" . $_POST['channel'] . "\n";

        AsteriskAccess::generateCallFile($call);

        echo json_encode([
            'success' => true,
            'msg'     => 'Start Spy',

        ]);
    }

    public function setAttributesModels($attributes, $models)
    {

        if (isset($attributes[0])) {
            $modelSip     = Sip::find()->all();;
            $modelServers = Servers::find('type != :key1 AND status IN (1,4) AND host != :key', [':key' => 'localhost', ':key1' => 'sipproxy'])->all();

            if (! isset($modelServers[0])) {
                array_push($modelServers, [
                    'name'     => 'Master',
                    'host'     => 'localhost',
                    'type'     => 'mbilling',
                    'username' => 'magnus',
                    'password' => 'magnussolution',
                ]);
            }

            $array   = '';
            $totalUP = 0;
            $i       = 1;
            foreach ($modelServers as $key => $server) {
                if ($server['type'] == 'mbilling') {
                    $server['host'] = 'localhost';
                }

                $modelCallOnLine = CallOnLine::find('server = :key', [':key' => $server['host']])->count();
                $modelCallOnLineUp = CallOnLine::find('server = :key AND status = :key1', ['key' => $server['host'], ':key1' => 'Up'])->count();

                $totalUP += $modelCallOnLineUp;
                $array .= '<font color="black">' . strtoupper($server['name']) . '</font> <font color="blue">T:' . $modelCallOnLine . '</font> <font color="green">A:' . $modelCallOnLineUp . '</font>&ensp;|&ensp;';

                if ($i % 13 == 0) {
                    $array .= "<br>";
                }
                $i++;
            }

            $attributes[0]['serverSum'] = $array;

            if ($totalUP > 0) {
                $attributes[0]['serverSum'] .= "<font color=green> TOTAL UP: " . $totalUP . "</font>";
            }
        }

        return $attributes;
    }
}
