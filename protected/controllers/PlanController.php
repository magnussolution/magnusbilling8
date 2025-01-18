<?php

/**
 * Acoes do modulo "Plan".
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
use app\models\Plan;

class PlanController extends CController
{

    public $extraValues    = ['idUser' => 'username'];

    public function init()
    {
        $this->instanceModel = new Plan();
        $this->abstractModel = Plan::find();
        $this->titleReport   = Yii::t('zii', 'Plan');
        $this->attributeOrder = $this->instanceModel::tableName() . '.name';
        parent::init();
    }
}
