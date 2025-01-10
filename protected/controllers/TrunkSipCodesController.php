<?php

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\TrunkSipCodes;

class TrunkSipCodesController extends CController
{

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function init()
    {
        $this->instanceModel = new TrunkSipCodes;
        $this->abstractModel = TrunkSipCodes::find();
        $this->attributeOrder = $this->instanceModel::tableName() . '.id DESC';
        parent::init();
    }
}
