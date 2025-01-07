<?php

/**
 * Acoes do modulo "CallBack".
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
use app\models\CallBack;

class CallBackController extends CController
{
    public $attributeOrder        = 't.id DESC';
    public $extraValues           = ['idUser' => 'username', 'idDid' => 'did'];
    public $fieldsInvisibleClient = [
        'variable',
    ];

    public function init()
    {
        $this->instanceModel = new CallBack;
        $this->abstractModel = CallBack::find();
        $this->titleReport   = Yii::t('app', 'CallBack');
        parent::init();
    }

    public function actionReprocesar($value = '')
    {
        # recebe os parametros para o filtro
        $filter = $_POST['filter'];

        $filter = $filter ? $this->createCondition(json_decode($filter)) : '';

        $filter = preg_replace('/t.status/', 'status', $filter);

        CallBack::model()->updateAll(['status' => '1', 'num_attempt' => 0, 'sessiontime' => 0], $filter, $this->paramsFilter);
        echo json_encode([
            'success' => true,
            'msg'     => $this->msgSuccess,
        ]);
    }
}
