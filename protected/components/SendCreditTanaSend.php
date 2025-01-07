<?php

/**
 * Class to send credit to mobile via Ding API
 *
 * MagnusBilling <info@magnusbilling.com>
 * 16/01/2021
 */

namespace app\components;

use app\components\LoadConfig;

class SendCreditTanaSend
{

    public static function sendCredit($number, $modelSendCreditRates, $type, $id, $test)
    {

        $config = LoadConfig::getConfig();

        $userBD = $config['global']['BDService_username'];
        $keyBD  = $config['global']['BDService_token'];

        $number = preg_replace('/^00/', '', $number);
        $number = preg_replace('/^88/', '', $number);

        if (preg_match('/\-/', $modelSendCreditRates->idProduct->product)) {
            $send_value = $_POST['TransferToMobile']['amountValuesBDT'];
        } else {
            $send_value = $modelSendCreditRates->idProduct->product;
        }

        $url = "http://takasend.org/ezzeapi/request/flexiload?number=" . $number . "&amount=" . $send_value . "&type=1&id=" . $id . "&user=" . $userBD . "&key=" . $keyBD;

        $result = file_get_contents($url);
        if (preg_match('/SUCCESS/', $result)) {
            return 'error_txt=Transaction successful=TanaSend=' . $id . '=' . $send_value;
        } else {
            return 'error_txt=Error' . print_r($result, true);
        }
    }
}
