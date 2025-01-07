<?php

/**
 * Acoes do modulo "Refillprovider".
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
use app\models\Refillprovider;
use app\models\Provider;

class RefillproviderController extends CController
{
    public $attributeOrder = 'id';
    public $extraValues    = ['idProvider' => 'provider_name'];
    public $filterByUser   = false;
    public $fieldsFkReport = [
        'id_provider' => [
            'table'       => 'pkg_provider',
            'pk'          => 'id',
            'fieldReport' => 'provider_name',
        ],
    ];

    public function init()
    {
        $this->instanceModel = new Refillprovider;
        $this->abstractModel = Refillprovider::find();
        $this->titleReport   = Yii::t('app', 'Refill Providers');

        parent::init();
    }

    public function afterSave($model, $values)
    {
        if ($this->isNewRecord) {
            $resultProvider     = Provider::model()->findByPk((int) $model->id_provider);
            $creditOld          = $resultProvider->credit;
            $model->description = $model->description . ', ' . Yii::t('app', 'Old credit') . ' ' . round($creditOld, 2);

            //add credit
            $resultProvider->credit = $model->credit > 0 ? $resultProvider->credit + $model->credit : $resultProvider->credit - ($model->credit * -1);
            $resultProvider->saveAttributes(['credit' => $resultProvider->credit]);
        }
        return;
    }
}
