<?php

/**
 * Actions of module "UserType".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\UserType;

class UserTypeController extends CController
{
    public $attributeOrder;

    public function init()
    {
        $this->instanceModel = new UserType;
        $this->abstractModel = UserType::find();
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }
}
