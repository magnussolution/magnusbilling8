<?php

/**
 * Acoes do modulo "Campaign".
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
use app\models\Offer;

class OfferController extends CController
{
    public $attributeOrder;
    public $filterByUser   = false;
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
        $this->instanceModel = new Offer;
        $this->abstractModel = Offer::find();
        $this->titleReport   = Yii::t('app', 'Offer');
        if (Yii::$app->session['isAdmin']) {
            $this->defaultFilter = '(pkg_offer.id_user < 2 || pkg_offer.id_user IS NULL)';
        }
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function extraFilterCustomAgent($filter)
    {
        $filter                       = 'pkg_offer.id_user = :agfby';
        $this->paramsFilter[':agfby'] = Yii::$app->session['id_user'];

        return $filter;
    }
    public function beforeSave($values)
    {
        if (Yii::$app->session['isAgent']) {
            $values['id_user'] = Yii::$app->session['id_user'];
        }
        return $values;
    }
}
