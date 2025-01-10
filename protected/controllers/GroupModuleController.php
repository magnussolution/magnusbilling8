<?php

/**
 * Actions of module "GroupModule".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\GroupModule;

class GroupModuleController extends CController
{
    public $titleReport    = 'GroupModule';
    public $subTitleReport = 'GroupModule';
    public $extraValues    = array('idGroup' => 'name', 'idModule' => 'text');
    public $filterByUser   = false;
    public $fieldsFkReport = array(
        'id_group'  => array(
            'table'       => 'group_user',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
        'id_module' => array(
            'table'       => 'module',
            'pk'          => 'id',
            'fieldReport' => 'text',
        ),
    );

    public function init()
    {
        $this->instanceModel = new GroupModule;
        $this->abstractModel = GroupModule::find();
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }
}
