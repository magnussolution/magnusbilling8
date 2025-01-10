<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\CampaignLog;

class CampaignLogController extends CController
{

    public function init()
    {
        $this->instanceModel = new CampaignLog;
        $this->abstractModel = CampaignLog::find();
        $this->titleReport   = Yii::t('app', 'CampaignLog');
        $this->attributeOrder = $this->instanceModel::tableName() . '.date DESC';
        parent::init();
    }
}
