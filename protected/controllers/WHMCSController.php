<?php

/**
 * Acoes do modulo "WHMCS".
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
use app\models\WHMCS;

class WHMCSController extends CController
{


    public $extraValues    = ['idUser' => 'username'];
    public function init()
    {
        $this->instanceModel = new WHMCS;
        $this->abstractModel = WHMCS::find();
        $this->titleReport   = Yii::t('zii', 'WHMCS');

        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }
}
