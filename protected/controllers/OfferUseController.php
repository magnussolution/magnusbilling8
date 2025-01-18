<?php

/**
 * Acoes do modulo "OfferUse".
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
use app\models\OfferUse;

class OfferUseController extends CController
{
    public $attributeOrder;
    public $extraValues    = ['idOffer' => 'label', 'idUser' => 'username'];

    public $fieldsFkReport = [
        'id_user'  => [
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ],
        'id_offer' => [
            'table'       => 'pkg_offer',
            'pk'          => 'id',
            'fieldReport' => 'label',
        ],
    ];

    public function init()
    {
        $this->instanceModel = new OfferUse;
        $this->abstractModel = OfferUse::find();
        $this->titleReport   = Yii::t('zii', 'Offer Use');

        if (Yii::$app->session['isAdmin']) {
            $this->relationFilter['idUser'] = [
                'condition' => "idUser.id_user < 2",
            ];
        }
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }
}
