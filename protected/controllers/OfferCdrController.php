<?php

/**
 * Acoes do modulo "OfferCdr".
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
use app\models\OfferCdr;

class OfferCdrController extends CController
{
    public $attributeOrder = 'date_consumption DESC';
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
        $this->instanceModel = new OfferCdr;
        $this->abstractModel = OfferCdr::find();
        $this->titleReport   = Yii::t('app', 'Offer') . ' CDR';

        if (Yii::$app->session['isAdmin']) {
            $this->relationFilter['idOffer'] = [
                'condition' => "(idOffer.id_user < 2 OR idOffer.id_user IS NULL)",
            ];
        }

        /*Aplica filtro padrao por data e causa de temrinao*/
        $filter         = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
        $filter         = $this->createCondition(json_decode($filter));
        $whereStarttime =  ! preg_match("/date_consumption/", $filter) ? ' AND date_consumption > "' . date('Y-m-d') . '"' : false;
        //$this->filter = $whereStarttime;
        parent::init();
    }

    public function extraFilterCustomAgent($filter)
    {
        //se é agente filtrar pelo user.id_user
        if (array_key_exists('idOffer', $this->relationFilter)) {
            $this->relationFilter['idOffer']['condition'] .= " AND idOffer.id_user = :agfby";
        } else {
            $this->relationFilter['idOffer'] = [
                'condition' => "idOffer.id_user = :agfby",
            ];
        }
        $this->paramsFilter[':agfby'] = Yii::$app->session['id_user'];

        return $filter;
    }
}
