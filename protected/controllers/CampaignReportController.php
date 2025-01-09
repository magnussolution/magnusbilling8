<?php

/**
 * Acoes do modulo "CampaignReport".
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
use app\models\Campaign;
use app\models\CampaignReport;

class CampaignReportController extends CController
{
    public $attributeOrder = 't.id';
    public $defaultFilter  = 't.status = 1';
    private $interval      = 0;
    public function init()
    {
        $this->instanceModel = new Campaign;
        $this->abstractModel = Campaign::find();
        $this->titleReport   = Yii::t('app', 'Campaign Report');
        parent::init();
    }

    public function actionRead($asJson = true, $condition = null)
    {

        $filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;

        if (isset($filter[0]->field) && $filter[0]->field == 'interval') {
            switch ($filter[0]->value) {
                case 'day':
                    $this->interval = strtotime(date('Y-m-d'));
                    break;
                default:
                    $this->interval = strtotime('-1 ' . $filter[0]->value, strtotime(date('Y-m-d H:i:s')));
                    break;
            }
        } else {
            $this->interval = strtotime('-1 hour', strtotime(date('Y-m-d H:i:s')));
        }

        $_GET['filter'] = '';

        parent::actionRead();
    }
    public function getAttributesModels($models, $itemsExtras = array())
    {

        /*

        2 Pending (CANCEL CONGESTION BUSY CHANUNVALEBLE)

        3 answer REceive the 200 ok code

        4 user press any digit

        5 AMD

        7 mach with your campaign forward configuration

        total dialed = total AMD + total answer + total failed

         */
        $attributes = false;
        foreach ($models as $key => $item) {
            $attributes[$key]    = $item->attributes;
            $modelCampaignReport = CampaignReport::find()
                ->select(['count(*) as totalDialed'])
                ->where(['id_campaign' => $item->id])
                ->andWhere(['>', 'unix_timestamp', $this->interval])
                ->one();

            $attributes[$key]['totalDialed'] = $modelCampaignReport->totalDialed;

            $modelCampaignReport = CampaignReport::find()
                ->select(['count(*) as totalFailed'])
                ->where(['id_campaign' => $item->id, 'status' => 2])
                ->andWhere(['>', 'unix_timestamp', $this->interval])
                ->one();
            if ($modelCampaignReport->totalFailed == 0 || $attributes[$key]['totalDialed'] == 0) {
                $ratio = 0;
            } else {
                $ratio = @($modelCampaignReport->totalFailed / $attributes[$key]['totalDialed']) * 100;
            }

            $attributes[$key]['totalFailed'] = $modelCampaignReport->totalFailed . ' (' . number_format($ratio, 2) . '%)';

            $modelCampaignReport = CampaignReport::find()
                ->select(['count(*) as totalAmd'])
                ->where(['id_campaign' => $item->id, 'status' => 5])
                ->andWhere(['>', 'unix_timestamp', $this->interval])
                ->one();
            if ($modelCampaignReport->totalAmd == 0 || $attributes[$key]['totalDialed'] == 0) {
                $ratio = 0;
            } else {
                $ratio = @($modelCampaignReport->totalAmd / $attributes[$key]['totalDialed']) * 100;
            }

            $attributes[$key]['totalAmd'] = $modelCampaignReport->totalAmd . ' (' . number_format($ratio, 2) . '%)';

            $modelCampaignReport = CampaignReport::find()
                ->select(['count(*) as totalAnswered'])
                ->where(['id_campaign' => $item->id])
                ->andWhere(['status' => [3, 4, 5, 7]])
                ->andWhere(['>', 'unix_timestamp', $this->interval])
                ->one();
            if ($modelCampaignReport->totalAnswered == 0 || $attributes[$key]['totalDialed'] == 0) {
                $ratio = 0;
            } else {
                $ratio = @($modelCampaignReport->totalAnswered / $attributes[$key]['totalDialed']) * 100;
            }
            $attributes[$key]['totalAnswered'] = $modelCampaignReport->totalAnswered . ' (' . number_format($ratio, 2) . '%)';

            $modelCampaignReport = CampaignReport::find()
                ->select(['count(*) as totalPressDigit'])
                ->where(['id_campaign' => $item->id, 'status' => 4])
                ->andWhere(['>', 'unix_timestamp', $this->interval])
                ->one();
            if ($modelCampaignReport->totalPressDigit == 0 || $attributes[$key]['totalDialed'] == 0) {
                $ratio = 0;
            } else {
                $ratio = @($modelCampaignReport->totalPressDigit / $attributes[$key]['totalDialed']) * 100;
            }
            $attributes[$key]['totalPressDigit'] = $modelCampaignReport->totalPressDigit . ' (' . number_format($ratio, 2) . '%)';

            $modelCampaignReport = CampaignReport::find()
                ->select(['count(*) as transfered'])
                ->where(['id_campaign' => $item->id, 'status' => 7])
                ->andWhere(['>', 'unix_timestamp', $this->interval])
                ->one();
            if ($modelCampaignReport->transfered == 0 || $attributes[$key]['totalDialed'] == 0) {
                $ratio = 0;
            } else {
                $ratio = @($modelCampaignReport->transfered / $attributes[$key]['totalDialed']) * 100;
            }

            $attributes[$key]['transfered'] = $modelCampaignReport->transfered . ' (' . number_format($ratio, 2) . '%)';
        }

        return $attributes;
    }
}
