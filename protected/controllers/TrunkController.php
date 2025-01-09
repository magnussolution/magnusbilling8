<?php

/**
 * Acoes do modulo "Trunk".
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
use app\models\trunk;

class TrunkController extends CController
{
    public $extraValues    = ['idProvider' => 'provider_name'];
    public $nameFkRelated  = 'failover_trunk';
    public $attributeOrder = 'id';
    public $fieldsFkReport = [
        'id_provider'    => [
            'table'       => 'pkg_provider',
            'pk'          => 'id',
            'fieldReport' => 'provider_name',
        ],
        'failover_trunk' => [
            'table'       => 'pkg_trunk',
            'pk'          => 'id',
            'fieldReport' => 'trunkcode',
        ],
    ];
    public function init()
    {
        $this->instanceModel = new Trunk;
        $this->abstractModel = Trunk::find();
        $this->titleReport   = Yii::t('app', 'Trunk');

        parent::init();
    }

    public function beforeSave($values)
    {

        if ($this->isNewRecord) {
            if (isset($values['fromuser']) && strlen($values['fromuser']) == 0) {
                $values['fromuser'] = $values['user'];
            }
        }

        if ((isset($values['register']) && $values['register'] == 1 && isset($values['register_string']))
            && ! preg_match("/^.{3}.*:.{3}.*@.{5}.*\/.{3}.*/", $values['register_string'])
        ) {
            echo json_encode([
                'success' => false,
                'rows'    => [],
                'errors'  => [
                    'register'        => Yii::t('app', 'Invalid register string. Only use register option to Trunk authentication via user and password.'),
                    'register_string' => Yii::t('app', 'Invalid register string'),
                ],
            ]);
            exit();
        }

        if (isset($values['providerip'])) {
            $modelTrunk = Trunk::findOne((int) $values['id']);
            if (isset($values['providertech']) && $values['providertech'] != 'sip' && $values['providertech'] != 'iax2') {
                $values['providerip'] = $modelTrunk->host;
            }
        }

        if (isset($values['trunkcode'])) {
            $values['trunkcode'] = preg_replace("/ /", "-", $values['trunkcode']);
        }

        if (isset($values['allow'])) {
            $values['allow'] = preg_replace("/,0/", "", $values['allow']);
            $values['allow'] = preg_replace("/0,/", "", $values['allow']);
        }

        if (isset($values['status'])) {
            if ($values['status'] == 1) {
                $values['short_time_call'] = 0;
            }
        }

        return $values;
    }
    public function setAttributesModels($attributes, $models)
    {

        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return $attributes;
        }

        $trunkRegister = AsteriskAccess::instance()->sipShowRegistry();
        $trunkRegister = explode("\n", $trunkRegister['data']);

        $pkCount = is_array($attributes) || is_object($attributes) ? $attributes : [];
        for ($i = 0; $i < count($pkCount); $i++) {
            $modelTrunk                                = Trunk::findOne((int) $attributes[$i]['failover_trunk']);
            $attributes[$i]['failover_trunktrunkcode'] = isset($modelTrunk->id)
                ? $modelTrunk->trunkcode
                : Yii::t('app', 'Undefined');
            foreach ($trunkRegister as $key => $trunk) {
                if (preg_match("/" . $attributes[$i]['host'] . ".*" . $attributes[$i]['username'] . ".*Registered/", $trunk) && $attributes[$i]['providertech'] == 'sip') {
                    $attributes[$i]['registered'] = 1;
                    break;
                }
            }
        }

        return $attributes;
    }

    //failover_trunktrunkcode

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

        $select = 'trunkcode, user, secret, disallow, allow, directmedia, context, dtmfmode, insecure, nat, qualify, type, host, register_string, sip_config';
        $model  = Trunk::find()
            ->select($select)
            ->where(['providertech' => 'iax2', 'status' => 1])
            ->all();

        if (count($model)) {
            AsteriskAccess::instance()->writeAsteriskFile($model, '/etc/asterisk/iax_magnus.conf', 'trunkcode');
        }
    }

    public function afterUpdateAll($strIds)
    {
        $this->generateSipFile();
        return;
    }

    public function afterSave($model, $values)
    {
        $this->generateSipFile();
    }

    public function afterDestroy($values)
    {
        $this->generateSipFile();
    }
}
