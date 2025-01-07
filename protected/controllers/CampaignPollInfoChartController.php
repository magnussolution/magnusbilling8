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

class CampaignPollInfoChartController extends CController
{
    public $attributeOrder = 't.id';

    public function actionRead($asJson = true, $condition = null)
    {
        $filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;
        $filter = json_decode($filter[0]->value);

        $this->filter = $this->createCondition($filter);

        if (!preg_match('/id_campaign_poll/', $this->filter)) {
            echo json_encode(array(
                $this->nameRoot  => array(),
                $this->nameCount => 0,
            ));
            exit;
        }

        $records = CampaignPollInfo::model()->findAll(array(
            'select'    => 'id, resposta AS resposta2, COUNT( resposta ) AS sumresposta, id_campaign_poll',
            'join'      => $this->join,
            'condition' => $this->filter,
            'params'    => $this->paramsFilter,
            'order'     => 'resposta DESC',
            'group'     => 'resposta',

        ));

        echo json_encode(array(
            $this->nameRoot  => $this->getAttributesModels($records),
            $this->nameCount => count($records),
        ));
    }

    public function getAttributesModels($records, $itemsExtras = array())
    {
        $filterCampaignPoll = json_decode($_GET['filter']);
        $filterCampaignPoll = json_decode($filterCampaignPoll[0]->value);
        $condition          = '1';
        foreach ($filterCampaignPoll as $f) {

            if (!isset($f->type) || $f->field != 'id_campaign_poll') {
                continue;
            }
            $type  = $f->type;
            $field = $f->field;
            $value = $f->value;

            $value = implode(',', $value);

            $condition .= " AND id IN($value)";
        }

        $total_poll = count($filterCampaignPoll[0]->value);

        if (isset($records[0]['id_campaign_poll'])) {
            $model = CampaignPoll::model()->findAll($condition);

            $ids_campaign_poll = array();
            foreach ($model as $key => $campaign_poll) {
                $ids_campaign_poll[] = $campaign_poll->id_campaign;
            }

            //get all campaign phonebook
            $criteria = new CDbCriteria();
            $criteria->addInCondition('id_campaign', $ids_campaign_poll);

            $modelCampaignPhonebook = CampaignPhonebook::model()->findAll($criteria);
            $ids_phone_books        = array();
            foreach ($modelCampaignPhonebook as $key => $phonebook) {
                $ids_phone_books[] = $phonebook->id_phonebook;
            }

            $criteria = new CDbCriteria();
            $criteria->addInCondition('id_phonebook', $ids_phone_books);
            $criteria->addCondition('status = :key');
            $criteria->params[':key'] = 3;
            $modelPhoneNumber         = PhoneNumber::model()->count($criteria);

            if ($modelPhoneNumber == 0) {
                $modelPhoneNumber = 1;
            }

            $totalVotes = CampaignPollInfo::model()->count(array(
                'condition' => $this->filter,
                'params'    => $this->paramsFilter,
            ));

            for ($i = 0; $i < count($records); $i++) {
                $records[$i]['percentage']    = Yii::t('app', 'Votes') . ': ' . $records[$i]['sumresposta'] . ' - ' . number_format(($records[$i]['sumresposta'] * 100) / $totalVotes, 2) . '%';
                $records[$i]['resposta_name'] = $total_poll == 1 && strlen($model[0]['option' . $records[$i]['resposta2']]) > 0 ? $model[0]['option' . $records[$i]['resposta2']] : $records[$i]['resposta2'];
                $records[$i]['total_votos']   = '<b>' . Yii::t('app', 'Answered') . ':</b>' . $modelPhoneNumber;
                $records[$i]['total_votos'] .= '<br><b>' . Yii::t('app', 'Votes') . ':</b>' . $totalVotes;
                $records[$i]['total_votos'] .= '<br><b>' . Yii::t('app', 'Voted') . ': </b>' . number_format(($totalVotes * 100) / $modelPhoneNumber, 2) . '%';
            }
        } else {
        }

        return $records;
    }
}
