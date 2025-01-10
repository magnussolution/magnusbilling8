<?php

/**
 * Acoes do modulo "Diddestination".
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
use app\components\UserCreditManager;
use app\components\AsteriskAccess;
use app\models\Diddestination;
use app\models\Did;
use app\models\User;
use app\models\Sip;
use app\models\Ivr;
use app\models\Queue;
use app\models\DidUse;
use app\components\Mail;

class DiddestinationController extends CController
{
    public $attributeOrder;
    public $extraValues    = [
        'idUser'  => 'username',
        'idDid'   => 'did',
        'idIvr'   => 'name',
        'idQueue' => 'name',
        'idSip'   => 'name',
    ];

    public $fieldsFkReport = [
        'id_user' => [
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ],
        'id_ivr'  => [
            'table'       => 'pkg_ivr',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ],
        'id_queue' => [
            'table'       => 'pkg_queue',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ],
        'id_sip'  => [
            'table'       => 'pkg_sip',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ],

    ];

    public $fieldsInvisibleClient = [
        'id_user',
        'idUserusername',
    ];

    public function init()
    {
        $this->instanceModel = new Diddestination;
        $this->abstractModel = Diddestination::find();
        $this->titleReport   = Yii::t('app', 'DID Destination');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function beforeSave($values)
    {

        $this->checkRelation($values);

        if ($this->isNewRecord) {

            $values['voip_call'] = isset($values['voip_call']) ? $values['voip_call'] : 1;

            $did       = Did::findOne($values['id_did']);
            $modelUser = User::findOne($values['id_user']);

            if (isset($modelUser->idGroup->idUserType->id) && $modelUser->idGroup->idUserType->id != 3) {
                echo json_encode([
                    'success' => false,
                    'rows'    => '[]',
                    'errors'  => Yii::t('app', 'You only can set DID to CLIENTS'),
                ]);
                exit;
            }

            if ($did->reserved == 0) {
                $priceDid = $did->connection_charge + $did->fixrate;

                $modelUser->credit = $modelUser->credit + $modelUser->creditlimit;
                if ($modelUser->credit < $priceDid) {
                    echo json_encode([
                        'success' => false,
                        'rows'    => '[]',
                        'errors'  => Yii::t('app', 'Customer not have credit for buy DID') . ' - ' . $did->did,
                    ]);
                    exit;
                }
            }
        }

        return $values;
    }

    public function checkRelation($values)
    {
        if ($this->isNewRecord) {

            switch ($values['voip_call']) {
                case '1':
                    $model = Sip::findOne((int) $values['id_sip']);
                    $name  = 'SIP ACCOUNT';
                    break;
                case '2':
                    $model = Ivr::findOne((int) $values['id_ivr']);
                    $name  = 'IVR';
                    break;
                case '7':
                    $model = Queue::findOne((int) $values['id_queue']);
                    $name  = 'QUEUE';
                    break;
            }

            if (isset($name) && $values['id_user'] != $model->id_user) {
                echo json_encode([
                    'success' => false,
                    'rows'    => [],
                    'errors'  => ['voip_call' => ['The ' . $name . ' must belong to the DID owner']],
                ]);
                exit;
            }
        } else {

            $modelDiddestination = Diddestination::findOne((int) $values['id']);

            $id_user = $modelDiddestination->id_user;

            $voip_call = isset($values['voip_call']) ? $values['voip_call'] : $modelDiddestination->voip_call;

            switch ($voip_call) {
                case '1':
                    $id_sip = isset($values['id_sip']) ? $values['id_sip'] : $modelDiddestination->id_sip;
                    $model  = Sip::findOne((int) $id_sip);
                    $name   = 'SIP ACCOUNT';
                    break;
                case '2':
                    $id_ivr = isset($values['id_ivr']) ? $values['id_ivr'] : $modelDiddestination->id_ivr;
                    $model  = Ivr::findOne((int) $id_ivr);
                    $name   = 'IVR';
                    break;
                case '7':
                    $id_queue = isset($values['id_queue']) ? $values['id_queue'] : $modelDiddestination->id_queue;
                    $model    = Queue::findOne((int) $id_queue);
                    $name     = 'QUEUE';
                    break;
            }

            if (isset($name) && isset($model->id_user) && $id_user != $model->id_user) {
                echo json_encode([
                    'success' => false,
                    'rows'    => [],
                    'errors'  => ['voip_call' => ['The ' . $name . ' must belong to the DID owner']],
                ]);
                exit;
            }
        }
    }

    public function actionbulkdestinatintion()
    {
        $this->isNewRecord = true;
        $values            = $this->getAttributesRequest();

        $_GET['filter'] = $values['filters'];

        $id_user = $values['id_user'];

        $this->setfilter($_GET);

        $modelDid = Did::find($this->filter, $this->paramsFilter)->all();

        foreach ($modelDid as $key => $did) {

            $values['id_did'] = $did->id;
            $destination      = preg_replace('/\{DID\}/', $did->did, $values['destination']);

            if ($did->id_user == null && $did->reserved == 0) {

                //isnewDID

                $modelDiddestination            = new Diddestination();
                $modelDiddestination->id_did    = $did->id;
                $modelDiddestination->id_user   = $id_user;
                $modelDiddestination->voip_call = $values['voip_call'];
                $modelDiddestination->priority  = 1;
                if (strlen($values['destination']) && $values['destination'] != 'undefined') {
                    $modelDiddestination->destination = $destination;
                }
                if (strlen($values['id_ivr']) && $values['id_ivr'] != 'undefined') {
                    $modelDiddestination->id_ivr = $values['id_ivr'];
                }

                if (strlen($values['id_queue']) && $values['id_queue'] != 'undefined') {
                    $modelDiddestination->id_queue = $values['id_queue'];
                }

                if (strlen($values['id_sip']) && $values['id_sip'] != 'undefined') {
                    $modelDiddestination->id_sip = $values['id_sip'];
                }

                if (strlen($values['context']) && $values['context'] != 'undefined') {
                    $modelDiddestination->context = $values['context'];
                }
                if (! strlen($values['destination'])) {
                    $modelDiddestination->destination = '';
                }

                $values = $this->beforeSave($values);

                $modelDiddestination->save();

                $this->afterSave($modelDiddestination, $values);
            } else {
                //update destination

                $modelDiddestination = Diddestination::find()->where(['id_did' => $did->id])->one();

                if (isset($modelDiddestination)) {
                    if ($modelDiddestination->id_user == $id_user) {
                        //update destination
                        $modelDiddestination->voip_call = $values['voip_call'];
                        if (strlen($values['destination']) && $values['destination'] != 'undefined') {
                            $modelDiddestination->destination = $destination;
                        }
                        if (strlen($values['id_ivr']) && $values['id_ivr'] != 'undefined') {
                            $modelDiddestination->id_ivr = $values['id_ivr'];
                        }

                        if (strlen($values['id_queue']) && $values['id_queue'] != 'undefined') {
                            $modelDiddestination->id_queue = $values['id_queue'];
                        }

                        if (strlen($values['id_sip']) && $values['id_sip'] != 'undefined') {
                            $modelDiddestination->id_sip = $values['id_sip'];
                        }

                        if (strlen($values['context']) && $values['context'] != 'undefined') {
                            $modelDiddestination->context = $values['context'];
                        }

                        if (! strlen($values['destination'])) {
                            $modelDiddestination->destination = '';
                        }

                        $values = $this->beforeSave($values);
                        $modelDiddestination->save();
                    } else {
                        continue;
                    }
                }
            }
        }

        echo json_encode([
            $this->nameSuccess => $this->success,
            $this->nameMsg     => $this->msg,
        ]);
    }

    public function afterSave($model, $values)
    {
        AsteriskAccess::instance()->writeDidContext();

        if ($this->isNewRecord) {
            $modelDid = Did::findOne($model->id_did);

            if ($modelDid->id_user == null && $modelDid->reserved == 0) //se for ativaçao adicionar o pagamento e cobrar
            {
                $modelDid->reserved = 1;
                $modelDid->id_user  = $model->id_user;
                $modelDid->save();

                //discount credit of customer
                $priceDid = $modelDid->connection_charge + $modelDid->fixrate;

                if ($priceDid > 0) // se tiver custo
                {

                    $modelUser = User::findOne($model->id_user);

                    if ($modelUser->id_user == 1) //se for cliente do master
                    {
                        //adiciona a recarga e pagamento do custo de ativaçao
                        if ($modelDid->connection_charge > 0) {
                            UserCreditManager::releaseUserCredit(
                                $model->id_user,
                                $modelDid->connection_charge,
                                Yii::t('app', 'Activation DID') . '' . $modelDid->did,
                                0
                            );
                        }

                        UserCreditManager::releaseUserCredit(
                            $model->id_user,
                            $modelDid->fixrate,
                            Yii::t('app', 'Monthly payment DID') . '' . $modelDid->did,
                            0
                        );

                        $mail = new Mail(Mail::$TYPE_DID_CONFIRMATION, $model->id_user);
                        $mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $modelUser->credit);
                        $mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $modelDid->did);
                        $mail->replaceInEmail(Mail::$DID_COST_KEY, '-' . $modelDid->fixrate);
                        $mail->send();
                    } else {
                        //charge the agent
                        $modelUser         = User::findOne($modelUser->id_user);
                        $modelUser->credit = $modelUser->credit - $priceDid;
                        $modelUser->save();
                    }
                }

                //adiciona a recarga e pagamento
                $use              = new DidUse;
                $use->id_user     = $model->id_user;
                $use->id_did      = $model->id_did;
                $use->status      = 1;
                $use->month_payed = 1;
                $use->save();

                if (isset($mail)) {
                    $sendAdmin = $this->config['global ']['admin_received_email'] == 1 ? $mail->send($this->config['global ']['admin_email']) : null;
                }
            }
        }
        return;
    }

    public function afterDestroy($values)
    {
        return;
    }
}
