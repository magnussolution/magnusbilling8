<?php

/**
 * Acoes do modulo "Call".
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

//http://localhost/mbilling/index.php/callApp?number=5511999464731&user=24315&name=magnus&city=torres

//http://localhost/mbilling/index.php/callApp/getReturn?number=5511999464731&user=24315&name=magnus&city=torres

//http://localhost/mbilling/index.php/callApp/getReturn?id=269196
namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\PhoneNumber;
use app\models\User;
use app\models\Campaign;
use app\models\CampaignPhonebook;

class CallAppController extends CController
{

    public $user;
    public $name;
    public $city;
    public $destination;
    public $id_phonebook;

    public function int()
    {
        $this->destination  = isset($_GET['number']) ? $_GET['number'] : '';
        $this->user         = isset($_GET['user']) ? $_GET['user'] : '';
        $this->name         = isset($_GET['name']) ? $_GET['name'] : '';
        $this->city         = isset($_GET['city']) ? $_GET['city'] : '';
        $this->id_phonebook = $this->getIdPhoneBook();
        parent::int();
    }

    public function actionIndex()
    {

        if (! isset($_GET['number'])) {

            echo 'error, numer is necessary';
        } else {

            $modelPhoneNumber               = new PhoneNumber();
            $modelPhoneNumber->id_phonebook = $this->id_phonebook;
            $modelPhoneNumber->number       = $$this->destination;
            $modelPhoneNumber->name         = $this->name;
            $modelPhoneNumber->city         = $this->city;
            $modelPhoneNumber->status       = 1;
            $modelPhoneNumber->try          = 1;
            $modelPhoneNumber->save();
            $idNumber = $modelPhoneNumber->getPrimaryKey();

            $array = [
                'msg' => 'success',
                'id'  => $idNumber,
            ];

            echo json_encode($array);
        }
    }

    public function actionGetReturn()
    {

        if (! isset($_GET['id'])) {

            if (! isset($_GET['number'])) {
                echo 'error, numer is necessary';
                exit;
            }
            $modelPhoneNumber = PhoneNumber::find()
                ->where([
                    'id_phonebook' => $this->id_phonebook,
                    'number'       => $this->destination,
                    'name'         => $this->name,
                ])
                ->one();
        } else {
            $modelPhoneNumber = PhoneNumber::findOne((int) $_GET['id']);
        }

        if (isset($modelPhoneNumber->status)) {
            $status = $modelPhoneNumber->status;
            $msg    = 'success';
        } else {
            $status = '';
            $msg    = 'Invalid Number';
        }

        $array = [
            'msg'    => $msg,
            'status' => $status,
        ];

        echo json_encode($array);
    }

    public function getIdPhoneBook()
    {
        $modelUser = User::find()->where(['username' => $this->user])->one();

        if (! isset($modelUser->id)) {
            $error_msg = Yii::t('app', 'Error : User no Found!');
            echo $error_msg;
            exit;
        }

        $id_user = $modelUser->id;

        $modelCampaign = Campaign::find()
            ->where(['status' => 1, 'id_user' => $id_user])
            ->one();

        if (is_array($modelUser) && isset($modelCampaign->id)) {

            $modelCampaignPhonebook = CampaignPhonebook::find()
                ->where(['id_campaign' => $modelCampaign->id])
                ->one();
        } else {
            echo "User not have campaign";
            exit;
        }

        if (! $modelCampaignPhonebook) {
            echo "Campaign not have PhoneBook";
            exit;
        }

        return $modelCampaignPhonebook->id_phonebook;
    }
}
