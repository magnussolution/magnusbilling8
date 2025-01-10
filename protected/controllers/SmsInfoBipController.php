<?php

namespace app\controllers;

use Yii;
use app\components\CController;

class SmsInfoBipController extends CController
{

    public function init()
    {

        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function actionSend()
    {
        //
    }
}
