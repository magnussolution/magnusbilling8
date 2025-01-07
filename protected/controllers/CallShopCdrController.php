<?php

/**
 * Acoes do modulo "CallShopCdr".
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
use app\models\CallShopCdr;

class CallShopCdrController extends CController
{
    public $attributeOrder = 't.date DESC';
    public $select         = 't.id, t.price_min, t.sessionid, t.destination, t.status, buycost, price, calledstation,
                    t.date, sessiontime, cabina, (((t.price - t.buycost) / t.buycost) * 100) markup';

    public $config;

    public function init()
    {

        if (isset($_GET['filters'])) {
            $_GET['filter'] = $_GET['filters'];
        }

        if (! Yii::$app->session['id_user']) {
            exit;
        }

        $this->instanceModel = new CallShopCdr;
        $this->abstractModel = CallShopCdr::find();
        $this->titleReport   = Yii::t('app', 'CallShop');
        parent::init();
    }

    public function applyFilterToLimitedAdmi2()
    {
        if (Yii::$app->session['user_type'] == 1 && Yii::$app->session['adminLimitUsers'] == true) {
            $this->relationFilter = [
                'idUser' => [
                    'condition' => "id_group IN (SELECT gug.id_group FROM pkg_group_user_group gug WHERE gug.id_group_user = :idgA0) ",
                ],
            ];
            $this->paramsFilter['idgA0'] = Yii::$app->session['id_group'];
        }
    }

    public function getSumPrice()
    {
        return $this->abstractModel->find(
            [
                'select'    => "SUM(price) price",
                'join'      => $this->join,
                'condition' => $this->filter,
                'params'    => $this->paramsFilter,
            ]
        );
    }

    public function beforeReport($columns)
    {
        //gerar total a pagar no pdf

        $modelCallShop = $this->getSumPrice();

        $this->titleReport    = $this->config['global']['base_currency'] . ' ' . round($modelCallShop->price, 2);
        $this->subTitleReport = Yii::t('app', 'Price SUM');

        $this->join          = '';
        $this->defaultFilter = 1;

        return $columns;
    }

    public function setAttributesModels($attributes, $models)
    {
        $modelCallShop = $this->getSumPrice();
        $pkCount       = is_array($attributes) || is_object($attributes) ? $attributes : [];
        for ($i = 0; $i < count($pkCount); $i++) {
            $attributes[$i]['priceSum'] = round($modelCallShop->price, 2);
        }
        return $attributes;
    }
}
