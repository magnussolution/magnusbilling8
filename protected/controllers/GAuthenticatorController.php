<?php

/**
 * Acoes do modulo "GAuthenticator".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2025 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\MagnusLog;
use app\models\GAuthenticator;
use app\models\User;

class GAuthenticatorController extends CController
{


    public function init()
    {
        $this->instanceModel = new GAuthenticator;
        $this->abstractModel = GAuthenticator::find();
        $this->titleReport   = Yii::t('zii', 'GAuthenticator');
        $this->attributeOrder = $this->instanceModel::tableName() . '.googleAuthenticator_enable DESC';
        parent::init();
    }

    public function beforeSave($values)
    {
        $modelUser = User::findOne((int) $values['id']);

        //try disable token from account
        if ($values['googleAuthenticator_enable'] != 1 && strlen($modelUser->google_authenticator_key) > 5) {
            $values = $this->googleAuthenticator($modelUser, $values);
        } else {
            if ($modelUser->googleAuthenticator_enable == 1 && strlen($modelUser->google_authenticator_key) > 5) {
                $modelUser->google_authenticator_key = '';
            }

            $modelUser->googleAuthenticator_enable = $modelUser->googleAuthenticator_enable == 1 ? 2 : $modelUser->googleAuthenticator_enable;
            if ($modelUser->googleAuthenticator_enable == 0) {
                $modelUser->google_authenticator_key = '';
            }
        }
        return $values;
    }
    public function googleAuthenticator($modelUser, $values)
    {
        require_once 'lib/GoogleAuthenticator/GoogleAuthenticator.php';

        $ga          = new PHPGangsta_GoogleAuthenticator();
        $secret      = $modelUser->google_authenticator_key;
        $oneCodePost = $values['code'];
        $checkResult = $ga->verifyCode($secret, $oneCodePost, 2);

        if (! $checkResult) {
            echo json_encode([
                'success' => false,
                'rows'    => [],
                'errors'  => Yii::t('zii', 'Invalid Code'),
            ]);
            $info = 'Username ' . Yii::$app->session['username'] . ' try inactive GoogleToken with Invalid Code to user ' . $modelUser->username;
            MagnusLog::insertLOG(2, $info);
            exit;
        } else {
            $values['google_authenticator_key'] = '';
        }

        return $values;
    }
}
