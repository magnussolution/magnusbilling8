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
use app\models\Campaign;
use app\models\CampaignPhonebook;
use app\models\CallOnLine;
use app\models\CampaignReport;
use app\models\PhoneNumber;

class CampaignDashBoardController extends CController
{
    public $extraValues    = ['idUser' => 'username'];
    private $uploaddir;

    public function init()
    {
        $this->instanceModel = new Campaign;
        $this->abstractModel = Campaign::find();
        $this->titleReport   = Yii::t('zii', 'Campaign');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id DESC';
        parent::init();
    }

    public function extraFilterCustomClient($filter)
    {
        $filter .= ' AND t.id_user = :clfby AND status = :key';

        $this->paramsFilter[':clfby'] = Yii::$app->session['id_user'];
        $this->paramsFilter[':key']   = 1;

        return $filter;
    }

    public function setAttributesModels($attributes, $models)
    {

        $pkCount = is_array($attributes) || is_object($attributes) ? $attributes : [];
        for ($i = 0; $i < count($pkCount); $i++) {
            //get all campaign phonebook
            $modelCampaignPhonebook = CampaignPhonebook::find('id_campaign = :key', [':key' => $attributes[$i]['id']])->all;

            $ids_phone_books = [];
            foreach ($modelCampaignPhonebook as $key => $phonebook) {
                $ids_phone_books[] = $phonebook->id_phonebook;
            }

            //Calls Being Placed
            $modelCallOnline = CallOnLine::find(
                'id_user = :key AND sip_account LIKE :key1 ',
                [
                    ':key'  => $attributes[$i]['id_user'],
                    ':key1' => 'MC!' . $attributes[$i]['name'] . '%',
                ]
            )->count();
            $attributes[$i]['callsPlaced'] = $modelCallOnline;

            // Calls Ringing
            $modelCallOnline = CallOnLine::find(
                'id_user = :key AND status LIKE :key1 ',
                [
                    ':key'  => $attributes[$i]['id_user'],
                    ':key1' => 'Ring%',
                ]
            )->count();
            $attributes[$i]['callsringing'] = $modelCallOnline;

            //Calls in Transfer
            $modelCallOnline = CallOnLine::find(
                'id_user = :key AND status = :key1 ',
                [
                    ':key'  => $attributes[$i]['id_user'],
                    ':key1' => 'Up',
                ]
            )->count();
            $attributes[$i]['callsInTransfer'] = $modelCallOnline;

            //Calls Transfered

            $modelPhoneNumber                  = PhoneNumber::find('info LIKE "Forward DTMF%" AND id_phonebook IN (' . $ids_phone_books . ')')->count();
            $attributes[$i]['callsTransfered'] = $modelPhoneNumber;

            //Total Numbers
            $modelPhoneNumber                    = PhoneNumber::find('id_phonebook IN (' . $ids_phone_books . ')')->count();
            $attributes[$i]['callsTotalNumbers'] = $modelPhoneNumber;

            //Diales Today
            $modelCdr = CampaignReport::find()(
                'unix_timestamp > :key AND id_campaign = :key1',
                [
                    ':key'  => strtotime(date('Y-m-d')),
                    ':key1' => $attributes[$i]['id'],
                ]
            )->count();
            $attributes[$i]['callsDialedtoday'] = $modelCdr;

            $modelPhoneNumber                      =  PhoneNumber::find('id_phonebook IN (' . $ids_phone_books . ') AND status = 1')->count();
            $attributes[$i]['callsRemaningToDial'] = $modelPhoneNumber;
        }

        return $attributes;
    }
}
