<?php

/**
 * Acoes do modulo "Call".
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
use app\components\AsteriskAccess;
use app\models\Servers;
use app\models\ServersServers;
use app\models\Trunk;
use CDbConnection;
use Exception;

class ServersController extends CController
{
    public $attributeOrder ;

    public $nameModelRelated   = 'ServersServers';
    public $nameFkRelated      = 'id_proxy';
    public $nameOtherFkRelated = 'id_server';

    public function init()
    {
        $this->instanceModel        = new Servers;
        $this->abstractModel        = Servers::find();
        $this->titleReport          = Yii::t('app', 'CallerID');
        $this->abstractModelRelated = ServersServers::find();
        $this->instanceModelRelated = new ServersServers;
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function setAttributesModels($attributes, $models)
    {
        $modelServer = Servers::find()
            ->where(['type' => 'asterisk', 'status' => 1])
            ->andWhere(['>', 'weight', 0])
            ->orderBy(['last_call' => SORT_DESC])
            ->one();
        if (isset($modelServer->id)) {
            $last_call = date("Y-m-d H:i:s", strtotime("-5 minutes", strtotime($modelServer->last_call)));

            $pkCount = is_array($attributes) || is_object($attributes) ? $attributes : [];
            for ($i = 0; $i < count($pkCount); $i++) {

                if ($attributes[$i]['status'] == 4) {
                    Servers::updateAll(['status' => 1], ['id' => $attributes[$i]['id']]);
                }
                if ($attributes[$i]['type'] == 'asterisk' && $attributes[$i]['status'] > 0 && $attributes[$i]['weight'] > '0' && $attributes[$i]['last_call'] < $last_call) {
                    Servers::updateAll(['status' => 4], ['id' => $attributes[$i]['id']]);
                }
            }
        }
        return $attributes;
    }

    public function afterSave($model, $values)
    {

        $modelServer = Servers::find()->query("type = 'sipproxy' AND status = 1")->all();
        foreach ($modelServer as $key => $proxy) {

            $hostname = $proxy->host;
            $dbname   = 'opensips';
            $table    = 'dispatcher';
            $user     = $proxy->username;
            $password = $proxy->password;
            $port     = $proxy->port;

            $dsn = 'mysql:host=' . $hostname . ';dbname=' . $dbname;

            try {
                $con = new CDbConnection($dsn, $user, $password);
            } catch (Exception $e) {
                return;
            }

            $con->active = true;

            $sql = "TRUNCATE $dbname.$table";
            $con->createCommand($sql)->execute();

            $modelServerAS = ServersServers::find()->query("id_proxy = :key", [':key' => $proxy->id]);

            if (isset($modelServerAS[0]->id_server)) {
                foreach ($modelServerAS as $key => $server) {

                    $modelServer = Servers::find()
                        ->where(['id' => $server->id_server])
                        ->andWhere(['or', ['type' => 'asterisk'], ['type' => 'mbilling']])
                        ->andWhere(['status' => [1, 4]])
                        ->andWhere(['>', 'weight', 0])
                        ->one();

                    if (isset($modelServer->id)) {
                        if ($this->ip_is_private($hostname)) {
                            $sql = "INSERT INTO $dbname.$table (setid,destination,weight,description) VALUES ('1','sip:" . $modelServer->host . ":" . $modelServer->sip_port . "','" . $modelServer->weight . "','" . $modelServer->description . "')";
                        } else {
                            $sql = "INSERT INTO $dbname.$table (setid,destination,weight,description) VALUES ('1','sip:" . $modelServer->public_ip . ":" . $modelServer->sip_port . "','" . $modelServer->weight . "','" . $modelServer->description . "')";
                        }

                        try {
                            $con->createCommand($sql)->execute();
                        } catch (Exception $e) {
                            return;
                        }
                    }
                }
            } else {

                $modelServerAS = Servers::find()
                    ->where(['or', ['type' => 'asterisk'], ['type' => 'mbilling']])
                    ->andWhere(['status' => [1, 4]])
                    ->andWhere(['>', 'weight', 0])
                    ->all();
                foreach ($modelServerAS as $key => $server) {

                    if ($this->ip_is_private($hostname)) {
                        $sql = "INSERT INTO $dbname.$table (setid,destination,weight,description) VALUES ('1','sip:" . $server->host . ":" . $server->sip_port . "','" . $server->weight . "','" . $server->description . "')";
                    } else {
                        $sql = "INSERT INTO $dbname.$table (setid,destination,weight,description) VALUES ('1','sip:" . $server->public_ip . ":" . $server->sip_port . "','" . $server->weight . "','" . $server->description . "')";
                    }

                    try {
                        $con->createCommand($sql)->execute();
                    } catch (Exception $e) {
                        return;
                    }
                }
            }
        }

        $this->generateSipFile();
    }

    public function generateSipFile()
    {

        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return;
        }
        $select = 'trunkcode, user, secret, disallow, allow, directmedia, context, dtmfmode, insecure, nat, qualify, type, host, fromdomain, fromuser, register_string, port, transport, encryption, sendrpid, maxuse, sip_config';
        $model  = Trunk::find()
            ->select($select)
            ->where(['providertech' => 'sip', 'status' => 1])
            ->all();

        if (count($model)) {
            AsteriskAccess::instance()->writeAsteriskFile($model, '/etc/asterisk/sip_magnus.conf', 'trunkcode');
        }
    }

    public function afterUpdateAll($strIds)
    {
        $this->generateSipFile();
        return;
    }

    public function afterDestroy($values)
    {
        $this->generateSipFile();
    }

    public function ip_is_private($ip)
    {
        $pri_addrs = [
            '10.0.0.0|10.255.255.255', // single namespace app\controllers;

use Yii;
use app\components\CController;

class A network
            '172.16.0.0|172.31.255.255', // 16 contiguous namespace app\controllers;

use Yii;
use app\components\CController;

class B network
            '192.168.0.0|192.168.255.255', // 256 contiguous namespace app\controllers;

use Yii;
use app\components\CController;

class C network
            '169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
            '127.0.0.0|127.255.255.255', // localhost
        ];

        $long_ip = ip2long($ip);
        if ($long_ip != -1) {

            foreach ($pri_addrs as $pri_addr) {
                list($start, $end) = explode('|', $pri_addr);

                // IF IS PRIVATE
                if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end)) {
                    return true;
                }
            }
        }

        return false;
    }
}
