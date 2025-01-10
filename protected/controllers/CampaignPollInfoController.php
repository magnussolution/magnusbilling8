<?php

/**
 * Acoes do modulo "CampaignPollInfo".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\CampaignPollInfo;

class CampaignPollInfoController extends CController
{
    public $attributeOrder;
    public $extraValues    = ['idCampaignPoll' => 'name'];

    public $nameFileReport = 'exported';

    public $fieldsFkReport = [
        'id_campaign_poll' => [
            'table'       => 'pkg_campaign_poll',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ],
    ];

    public function init()
    {
        $this->instanceModel = new CampaignPollInfo;
        $this->abstractModel = CampaignPollInfo::find();
        $this->titleReport   = Yii::t('app', 'Poll Info');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function applyFilterToLimitedAdmin()
    {
        if (Yii::$app->session['user_type'] == 1 && Yii::$app->session['adminLimitUsers'] == true) {
            $this->join .= ' JOIN pkg_campaign_poll cp ON cp.id_user = t.id';
            $this->join .= ' JOIN pkg_user ub ON cp.id_user = ub.id';
            $this->filter .= " AND ub.id_group IN (SELECT gug.id_group
                                FROM pkg_group_user_group gug
                                WHERE gug.id_group_user = :idgA0)";

            $this->paramsFilter['idgA0'] = Yii::$app->session['id_group'];
        }
    }

    public function extraFilterCustomClient($filter)
    {
        $this->join .= 'JOIN pkg_campaign_poll cp ON cp.id = id_campaign_poll';
        $filter .= ' AND cp.id_user = :clfby';
        $this->paramsFilter[':clfby'] = Yii::$app->session['id_user'];
        return $filter;
    }

    public function extraFilterCustomAgent($filter)
    {
        $this->join .= 'JOIN pkg_campaign_poll cp ON cp.id = id_campaign_poll';
        $this->join .= ' JOIN pkg_user user ON cp.id_user = user.id ';

        $filter .= ' AND user.id_user = :agfby';
        $this->paramsFilter[':agfby'] = Yii::$app->session['id_user'];

        return $filter;
    }

    public function subscribeColunms($columns = '')
    {

        for ($i = 0; $i < count($columns); $i++) {

            if ($columns[$i]['dataIndex'] == 'resposta') {
                $columns[$i]['header'] = 'DTMF';
            }
        }

        $columns[] = ['header' => 'Response', 'dataIndex' => 'resposta_text'];

        return $columns;
    }
}
