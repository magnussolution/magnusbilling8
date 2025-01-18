<?php

/**
 * Actions of module "Firewall".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 04/01/2025
 * Defaults!/usr/bin/fail2ban-client !requiretty
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\Firewall;

class FirewallController extends CController
{
    public function init()
    {
        $this->instanceModel = new Firewall;
        $this->abstractModel = Firewall::find();
        $this->titleReport   = Yii::t('zii', 'Firewall');
        $this->attributeOrder = $this->instanceModel::tableName() . '.date DESC';

        echo json_encode([
            $this->nameSuccess => $this->success,
            $this->nameRoot    => $this->attributes,
            $this->nameMsg     => $this->msg . 'This option has been discontinued.',
        ]);
    }
}
