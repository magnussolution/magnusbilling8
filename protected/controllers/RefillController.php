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

            $this->relationFilter['idUser'] = function ($query) {
                $query->andWhere(['<', 'pkg_user.id_user', '2']);
            };
        }

        $this->attributeOrder = $this->instanceModel::tableName() . '.date DESC';
        parent::init();
    }

    public function extraFilterCustomAgent($filter)
    {




        $this->relationFilter['idUser'] = function ($query) {
            $query->andWhere(['<', 'pkg_user.id_user', Yii::$app->session['id_user']]);
            $query->andWhere(['<', 'pkg_refill.id_user', Yii::$app->session['id_user']]);
        };


        return $filter;
    }

    public function beforeSave($values)
    {
        $modelRefill = Refill::findOne($values['id']);
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
            $modelUser = User::find()
                ->select(['SUM(credit) AS credit'])
                ->where(['id_user' => Yii::$app->session['id_user']])
                ->one();

            if (isset($values['credit'])) {
                $totalRefill = $modelUser->credit + $values['credit'];

                $modelUser = User::findOne((int) Yii::$app->session['id_user']);

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
            $modelRefill = Refill::findOne($values['id']);
            if (preg_match('/^PENDING\:/', $modelRefill->description) && $modelRefill->payment == 0 && $modelRefill->credit < 0) {
                $this->cancelSendCreditBDService($values, $modelRefill);
            }
        }
        return $values;
    }

    public function recordsExtraSum($records)
    {

        $query = $this->abstractModel;
        $query->select(['EXTRACT(YEAR_MONTH FROM date) AS CreditMonth', 'SUM(pkg_refill.credit) AS sumCreditMonth']);
        $query->join = $this->join;
        $query->where($this->filter);

        if (!empty($this->paramsFilter)) {
            $query->addParams($this->paramsFilter);
        }

        if (!empty($this->relationFilter)) {
            $query->joinWith($this->relationFilter);
        }
        $query->groupBy('CreditMonth');

        $this->nameSum = 'sum';

        return $query->one();
    }

    public function setAttributesModels($attributes, $models)
    {
        $query = $this->abstractModel;
        if (strlen($this->filter)) {
            $query->where($this->filter);
        }
        $query->select = ['SUM(pkg_refill.credit) AS credit'];
        $query->join   = $this->join;
        $query->params = $this->paramsFilter;
        $this->relationFilter['idUser'] = function ($query) {
            $query->andWhere(['<', 'pkg_user.id_user', '2']);
        };
        if (!empty($this->addInCondition)) {
            $query->andWhere($this->addInCondition);
        }
        $query->groupBy = null;
        $modelRefill = $query->one();



        $query = $this->abstractModel;
        $query->select = ['EXTRACT(YEAR_MONTH FROM date) AS CreditMonth', 'SUM(pkg_refill.credit) AS sumCreditMonth'];
        $query->groupBy = ['CreditMonth'];
        if (!empty($this->addInCondition)) {
            $query->andWhere($this->addInCondition);
        }
        // $query->groupBy = 'id_user';
        $modelRefillSumm2 = $query->all();

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

        User::updateAll(
            [
                'credit' => new \yii\db\Expression('credit + ' . $modelRefill->credit),
            ],
            ['id' => $modelRefill->id_user]
        );
    }
    public function releaseSendCreditBDService($values, $modelRefill)
    {

        User::updateAll(
            [
                'credit' => new \yii\db\Expression('credit - ' . $modelRefill->credit),
            ],
            ['id' => $modelRefill->id_user]
        );


        $modelRefill->description = preg_replace('/PENDING\: /', '', $modelRefill->description);
        $modelRefill->save();
    }
}
