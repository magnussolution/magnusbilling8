<?php

/**
 * Acoes do modulo "DidUse".
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
use app\models\ServicesUse;
use app\models\ServicesProcess;

class ServicesUseController extends CController
{
    public $extraValues    = ['idServices' => 'name,price,type', 'idUser' => 'username'];

    public $fieldsInvisibleClient = [
        'id_user',
        'reminded',
        'idUserusername',
    ];
    public function init()
    {
        $this->instanceModel = new ServicesUse;
        $this->abstractModel = ServicesUse::find();
        $this->titleReport   = Yii::t('zii', 'Services Use');

        $sql = "UPDATE pkg_services_use SET next_due_date = date_add(`reservationdate`, interval`month_payed`month)";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "UPDATE pkg_services_use SET next_due_date = '' WHERE status = 0";
        Yii::$app->db->createCommand($sql)->execute();

        $this->attributeOrder = $this->instanceModel::tableName() . '.status DESC, DAY( reservationdate ) DESC';
        parent::init();
    }

    public function actionCancelService()
    {
        ServicesProcess::release((int) $_REQUEST['id']);
    }
}
