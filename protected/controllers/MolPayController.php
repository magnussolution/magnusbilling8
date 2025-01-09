<?php

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\UserCreditManager;
use app\components\Util;
use app\models\Methodpay;
use app\models\User;
use app\models\Refill;

class MolPayController extends CController
{
    public function actionIndex()
    {

        /*$_POST = array(
        'skey'       => "bdaa623a10bd92ca393c069d81170f7b",
        'tranID'     => '22771468',
        'domain'     => 'teztelcom',
        'status'     => '00',
        'amount'     => '2.00',
        'currency'   => 'RM',
        'paydate'    => '2017-10-16 14:39:44',
        'orderid'    => '20171016043931-281060287519',
        'appcode'    => '',
        'error_code' => '',
        'error_desc' => '',
        'channel'    => 'FPX',
        );
         */
        Yii::error(print_r($_REQUEST, true), 'error');
        $filter = "payment_method = 'molpay'";
        $params = array();

        if (isset($_GET['agent'])) {
            $filter .= " AND u.username = :username";
            $params = array(':username' => addslashes(strip_tags(trim($_GET['agent']))));
        } else {
            $filter .= " AND u.id = 1";
        }
        $modelMethodpay = Methodpay::find()
            ->innerJoin('pkg_user u', 'methodpay.id_user = u.id')
            ->where($filter, $params)
            ->one();

        if (!count($modelMethodpay)) {
            Yii::error('Methos pay not found', 'error');
            exit;
        }

        $idUser = $modelMethodpay->idUser->id_user;
        $vkey   = $modelMethodpay->P2P_RecipientKeyID;

        if (count($_POST) > 0) {

            $tranID   = $_POST['tranID'];
            $orderid  = $_POST['orderid'];
            $status   = $_POST['status'];
            $domain   = $_POST['domain'];
            $amount   = $_POST['amount'];
            $currency = $_POST['currency'];
            $appcode  = $_POST['appcode'];
            $paydate  = $_POST['paydate'];
            $skey     = $_POST['skey'];

            // All undeclared variables below are coming from POST method
            $key0 = md5($tranID . $orderid . $status . $domain . $amount . $currency);
            $key1 = md5($paydate . $domain . $key0 . $appcode . $vkey);

            Yii::error(print_r($skey . ' ' . $key1, true), 'error');

            if ($skey != $key1) {
                $status = -1;
            }
            // invalid transaction
            //-------------------------------------------
            if ($status == "00") {

                Yii::error(print_r($status . 'OK 00', true), 'error');

                $identification = Util::getDataFromMethodPay($orderid);
                if (!is_array($identification)) {
                    exit;
                }

                $username = $identification['username'];
                $id_user  = $identification['id_user'];
                $status   = $_POST['status'];
                $amount   = str_replace(",", ".", $amount);
                $amount   = $amount * 0.9434;

                $description = "Received via MOLPAY:" . $tranID;
                Yii::error(print_r($description, true), 'error');

                Yii::error('username=' . $username . ', amount = ' . $amount, 'error');
                $modelUser = User::findOne((int) $id_user);

                if (isset($modelUser->id) && Refill::countRefill($tranID, $modelUser->id) == 0) {
                    UserCreditManager::releaseUserCredit($modelUser->id, $amount, $description, 1, $tranID);
                    echo "<p align='center'> <font color=red font face='verdana' size='5pt'>Your payment was completed.</font> </p>";
                    echo "<p align='center'> <font color=red font face='verdana' size='5pt'>You may close this window and get back to your account.</font> </p>";
                    echo "<p align='center'> <font color=red font face='verdana' size='5pt'>Thank You</font> </p>";
                }
            } else {
                // failure action
                echo 'Error';
                exit;
            }
        } else {
            echo '<h3>thanks.</h3>';
        }
    }
}
