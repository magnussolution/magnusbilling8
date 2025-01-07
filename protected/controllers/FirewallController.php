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

class FirewallController extends CController
{

    public $attributeOrder = 'date DESC';

    public function init()
    {

        echo json_encode([
            $this->nameSuccess => $this->success,
            $this->nameRoot    => $this->attributes,
            $this->nameMsg     => $this->msg . 'This option has been discontinued.',
        ]);
    }
}
