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
    public $attributeOrder = 't.date DESC';

    public function init()
    {
        $this->instanceModel = new CampaignLog;
        $this->abstractModel = CampaignLog::find();
        $this->titleReport   = Yii::t('app', 'CampaignLog');
        parent::init();
    }
}
