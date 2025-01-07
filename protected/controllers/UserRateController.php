<?php

/**
 * Actions of module "User".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\UserRate;

class UserRateController extends CController
{
    public $attributeOrder = 't.id DESC';
    public $titleReport    = 'User Rate';
    public $subTitleReport = 'User Rate';

    public $extraValues = array('idUser' => 'username', 'idPrefix' => 'destination,prefix');

    public $fieldsFkReport = array(
        'id_user'   => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
        'id_prefix' => array(
            'table'       => 'pkg_prefix',
            'pk'          => 'id',
            'fieldReport' => 'destination',
        ),
    );

    public function init()
    {
        $this->instanceModel = new UserRate;
        $this->abstractModel = UserRate::find();
        parent::init();
    }
}
