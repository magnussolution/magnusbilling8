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
use app\models\CallOnlineChart;
use app\models\StatusSystem;
use app\models\Refill;
use app\models\User;
use app\models\CallSummaryPerMonth;

class StatusSystemController extends CController
{

    public function init()
    {
        $this->instanceModel = new StatusSystem;
        $this->abstractModel = StatusSystem::find();
        $this->titleReport   = Yii::t('zii', 'Status system');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id DESC';
        parent::init();

        if (!Yii::$app->session['isAdmin']) {
            $this->extraValues = array(
                'idUser'   => 'username',
                'idPlan'   => 'name',
                'idPrefix' => 'destination',
            );
        }
    }

    public function setAttributesModels($attributes, $models)
    {

        $attributes[0]['totalActiveUsers'] = User::find()->where(['active' => 1])->count();
        $modelCallSummaryPerMonth = CallSummaryPerMonth::find()->where(['month' => date('Ym')])->one();

        $attributes[0]['monthprofit'] = isset($modelCallSummaryPerMonth->lucro) ? number_format($modelCallSummaryPerMonth->lucro, 2) : 0;

        $modelCallOnlineChart = CallOnlineChart::find()
            ->where(['>', 'date', date('Y-m-d')])
            ->orderBy(['total' => SORT_DESC])
            ->one();

        $totalCPS = StatusSystem::find()
            ->where(['>', 'date', date('Y-m-d 00:00:00')])
            ->orderBy(['cps' => SORT_DESC])
            ->one();

        $cps = empty($totalCPS->cps) ? 0 :  $totalCPS->cps;

        $attributes[0]['maximumcc'] = isset($modelCallOnlineChart->total) ? 'CC ' . $modelCallOnlineChart->total . ' | CPS ' . $cps : 'CC 0 | CPS ' . $cps;

        $modelRefill = Refill::find()
            ->select(['SUM(credit) as credit'])
            ->where(['>', 'date', date('Y-m') . '-01'])
            ->one();
        $attributes[0]['monthRefill'] = isset($modelRefill->credit) ? number_format($modelRefill->credit, 2) : 0;

        return $attributes;
    }
}
