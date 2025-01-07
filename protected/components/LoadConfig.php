<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @updated for Yii2 migration
 * @original_author Adilson Leffa Magnus.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

namespace app\components;

use app\models\Configuration;

class Loadconfig
{
    public static function getConfig()
    {
        $modelConfiguration = Configuration::find()->all();

        $config = [];
        foreach ($modelConfiguration as $conf) {
            $config[$conf->config_group_title][$conf->config_key] = $conf->config_value;
        }

        return $config;
    }
}
