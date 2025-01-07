<?php

/**
 * Acoes do modulo "Refill".
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
use app\components\Util;
use app\components\UserCreditManager;
use app\models\Refill;
use app\models\User;
use app\models\SendCreditSummary;

class RefillController extends CController
{
    public $attributeOrder = 'date DESC';
    public $extraValues    = ['idUser' => 'username'];

    public $fieldsFkReport = [
        'id_user' => [
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ],
    ];
    public $fieldsInvisibleClient = [
        'id_user',
        'idUserusername',
        'refill_type',
    ];

    public function init()
    {
        $this->instanceModel = new Refill;
        $this->abstractModel = Refill::find();
        $this->titleReport   = Yii::t('app', 'Refill');

        if (Yii::$app->session['isAdmin']) {
            $this->relationFilter['idUser'] = [
                'condition' => "idUser.id_user < 2",
            ];
        }

        parent::init();
    }

    public function extraFilterCustomAgent($filter)
    {
        if (array_key_exists('idUser', $this->relationFilter)) {
            $this->relationFilter['idUser']['condition'] .= " AND idUser.id_user LIKE :agfby";
        } else {
            $this->relationFilter['idUser'] = [
                'condition' => "t.id_user = :idagent5334 OR  idUser.id_user LIKE :agfby",
            ];
            $this->paramsFilter[':idagent5334'] = Yii::$app->session['id_user'];
        }
        $this->paramsFilter[':agfby'] = Yii::$app->session['id_user'];

        return $filter;
    }

    public function beforeSave($values)
    {
        $modelRefill = Refill::model()->findByPk($values['id']);
        if (! $this->isNewRecord) {

            if (isset($values['payment']) && (preg_match('/^PENDING\:/', $modelRefill->description) && $values['payment'] == 1 && $modelRefill->payment == 0)) {
                $this->releaseSendCreditBDService($values, $modelRefill);
            }
        }
        if (isset(Yii::$app->session['isAgent']) && Yii::$app->session['isAgent'] == true) {

            $id_user = isset($values['id_user']) ? $values['id_user'] : $modelRefill->id_user;

            if (Yii::$app->session['id_user'] == $id_user) {
                echo json_encode([
                    'success' => false,
                    'rows'    => [],
                    'errors'  => Yii::t('app', 'You cannot add credit to yourself'),
                ]);
                exit;
            }
            //get the total credit betewen agent users
            $modelUser = User::model()->find(
                [
                    'select'    => 'SUM(credit) AS credit',
                    'condition' => 'id_user = :key',
                    'params'    => [':key' => Yii::$app->session['id_user']],
                ]
            );

            if (isset($values['credit'])) {
                $totalRefill = $modelUser->credit + $values['credit'];

                $modelUser = User::model()->findByPk((int) Yii::$app->session['id_user']);

                $userAgent = $modelUser->typepaid == 1 ? $modelUser->credit = $modelUser->credit + $modelUser->creditlimit : $modelUser->credit;

                $maximunCredit = $this->config["global"]['agent_limit_refill'] * $userAgent;
                //Yii::error("$totalRefill > $maximunCredit", 'info');
                if ($totalRefill > $maximunCredit) {
                    $limite = $maximunCredit - $totalRefill;
                    echo json_encode([
                        'success' => false,
                        'rows'    => [],
                        'errors'  => Yii::t('app', 'Limit refill exceeded, your limit is') . ' ' . $maximunCredit . '. ' . Yii::t('app', 'You have') . ' ' . $limite . ' ' . Yii::t('app', 'to refill'),
                    ]);
                    exit;
                }
            }
        }

        return $values;
    }

    public function afterSave($model, $values)
    {
        if ($this->isNewRecord) {
            UserCreditManager::releaseUserCredit($model->id_user, $model->credit, $model->description, 2);
            if (preg_match("/Send Credit to /", $model->description)) {
                //Send Credit to 01788988066 via bkash at 107.50
                //Send Credit to 01717768732 via flexiload at 11.83. Ref: BD06120019120095

                if ($model->credit < 0) {
                    $credit = $model->credit * -1;
                } else {
                    $credit = $model->credit;
                }
                $service = explode(' ', $model->description);

                $number  = $service[3];
                $sell    = substr($service[7], 0, -1);
                $service = $service[5];

                $modelSendCreditSummary            = new SendCreditSummary();
                $modelSendCreditSummary->id_user   = $model->id_user;
                $modelSendCreditSummary->service   = $service;
                $modelSendCreditSummary->number    = $number;
                $modelSendCreditSummary->confirmed = $model->payment;
                $modelSendCreditSummary->cost      = $credit;
                $modelSendCreditSummary->sell      = $sell;
                $modelSendCreditSummary->amount    = $credit;

                $modelSendCreditSummary->earned = $sell - $credit;
                $modelSendCreditSummary->save();

                $model->invoice_number = $modelSendCreditSummary->id;
                $model->save();
            }
        }

        if (isset($_FILES["image"]) && strlen($_FILES["image"]["name"]) > 1) {

            unlink('/var/www/html/mbilling/resources/images/refill/' . $model->id . '.png');
            unlink('/var/www/html/mbilling/resources/images/refill/' . $model->id . '.jpeg');
            unlink('/var/www/html/mbilling/resources/images/refill/' . $model->id . '.jpg');

            $typefile = Util::valid_extension($_FILES["image"]["name"], ['png', 'jpeg', 'jpg']);

            $uploadfile = 'resources/images/refill/' . $model->id . '.' . $typefile;

            Yii::error(print_r($uploadfile, true), 'error');
            move_uploaded_file($_FILES["image"]["tmp_name"], $uploadfile);

            $model->image = $uploadfile;
            $model->save();
        }
        return;
    }

    public function beforeDestroy($values)
    {
        if (isset($values['id'])) {
            $modelRefill = Refill::model()->findByPk($values['id']);
            if (preg_match('/^PENDING\:/', $modelRefill->description) && $modelRefill->payment == 0 && $modelRefill->credit < 0) {
                $this->cancelSendCreditBDService($values, $modelRefill);
            }
        }
        return $values;
    }

    public function recordsExtraSum($records)
    {
        $criteria = new CDbCriteria([
            'select'    => 'EXTRACT(YEAR_MONTH FROM date) AS CreditMonth , SUM(t.credit) AS sumCreditMonth',
            'join'      => $this->join,
            'condition' => $this->filter == '' ? 1 : $this->filter,
            'params'    => $this->paramsFilter,
            'with'      => $this->relationFilter,
            'group'     => 'CreditMonth',
        ]);

        $this->nameSum = 'sum';

        return $this->abstractModel->findAll($criteria);
    }

    public function setAttributesModels($attributes, $models)
    {

        $criteria = new CDbCriteria();
        if (strlen($this->filter)) {
            $criteria->addCondition($this->filter);
        }
        $criteria->select = 'SUM(t.credit) AS credit';
        $criteria->join   = $this->join;
        $criteria->params = $this->paramsFilter;
        $criteria->with   = $this->relationFilter;

        if (count($this->addInCondition)) {
            $criteria->addInCondition($this->addInCondition[0], $this->addInCondition[1]);
        }

        $modelRefill = $this->abstractModel->find($criteria);

        $criteria->select = 'EXTRACT(YEAR_MONTH FROM date) AS CreditMonth , SUM(t.credit) AS sumCreditMonth';

        $criteria->group = 'CreditMonth';

        if (count($this->addInCondition)) {
            $criteria->addInCondition($this->addInCondition[0], $this->addInCondition[1]);
        }
        $modelRefillSumm2 = $this->abstractModel->findAll($criteria);

        $pkCount = is_array($attributes) || is_object($attributes) ? $attributes : [];
        for ($i = 0; $i < count($pkCount); $i++) {
            $attributes[$i]['sumCredit']      = number_format($modelRefill->credit, 2);
            $attributes[$i]['sumCreditMonth'] = $modelRefillSumm2[0]['sumCreditMonth'];
            $attributes[$i]['CreditMonth']    = substr($modelRefillSumm2[0]['CreditMonth'], 0, 4) . '-' . substr($modelRefillSumm2[0]['CreditMonth'], -2);
        }
        return $attributes;
    }

    public function cancelSendCreditBDService($values, $modelRefill)
    {
        User::model()->updateByPk(
            $modelRefill->id_user,
            [
                'credit' => new CDbExpression('credit + ' . $modelRefill->credit * -1),
            ]
        );
    }
    public function releaseSendCreditBDService($values, $modelRefill)
    {

        User::model()->updateByPk(
            $modelRefill->id_user,
            [
                'credit' => new CDbExpression('credit - ' . $modelRefill->credit * -1),
            ]
        );

        $modelRefill->description = preg_replace('/PENDING\: /', '', $modelRefill->description);
        $modelRefill->save();
    }

    public function subscribeColunms($columns = '')
    {
        if ($this->config['global']['base_country'] != 'BRL') {
            for ($i = 0; $i < count($columns); $i++) {

                if ($columns[$i]['dataIndex'] == 'credit') {
                    $columns[$i]['dataIndex'] = 't.credit';
                }
            }
        }
        return $columns;
    }
}
