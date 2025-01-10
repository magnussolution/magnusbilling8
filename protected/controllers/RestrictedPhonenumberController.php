<?php

/**
 * Acoes do modulo "RestrictedPhonenumber".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2023 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 17/08/2012
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\RestrictedPhonenumber;


class RestrictedPhonenumberController extends CController
{
    public $extraValues    = ['idUser' => 'username'];

    public $fieldsFkReport = [
        'id_user' => [
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ],
    ];

    public function init()
    {

        $this->instanceModel = new RestrictedPhonenumber;
        $this->abstractModel = RestrictedPhonenumber::find();
        $this->titleReport   = Yii::t('app', 'Refill Providers');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';

        parent::init();
    }

    public function importCsvSetAdditionalParams()
    {
        $values = $this->getAttributesRequest();
        return [['key' => 'id_user', 'value' => $values['id_user']]];
    }
}
