<?php

/**
 * Class to send credit to mobile via Ding API
 *
 * MagnusBilling <info@magnusbilling.com>
 * 16/01/2021
 */

namespace app\components;

use app\components\LoadConfig;

class SendCreditReload
{

    public function getToken()
    {
        $config = LoadConfig::getConfig();

        /*
        INSERT INTO pkg_configuration VALUES
        (NULL, 'Reloadly client_id', 'reloadly_client_id', '', 'Reloadly client_id', 'global', '1'),
        (NULL, 'Reloadly secret', 'reloadly_client_secret', '', 'Reloadly secret', 'global', '1');

         */
        $client_id     = $config['global']['reloadly_client_id'];
        $client_secret = $config['global']['reloadly_client_secret'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://auth.reloadly.com/oauth/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);

        if ($config['global']['reloadly_sandbox'] == 1) {
            $url = "https://topups-sandbox.reloadly.com";
        } else {
            $url = "https://topups.reloadly.com";
        }

        $requestBody = json_encode([
            "client_id"     => $client_id,
            "client_secret" => $client_secret,
            "grant_type"    => "client_credentials",
            "audience"      => $url,
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Accept: application/json",
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);

        $result->access_token;

        return $result->access_token;
    }

    public function getCountry($countryCode = '')
    {

        $ch       = curl_init();
        $response = curl_exec($ch);
        curl_close($ch);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://topups.reloadly.com/countries",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => [
                "Accept: application/com.reloadly.topups-v1+json",
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response);
        foreach ($result as $key => $country) {
            if ($country->currencyCode == $countryCode) {
                return $country->isoName;
            }
        }
    }

    public function getOperator($number = '', $access_token)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://topups.reloadly.com/operators/auto-detect/phone/+" . $number . "/countries/BR?&includeBundles=true");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/com.reloadly.topups-v1+json",
            "Authorization: Bearer " . $access_token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        return $result->operatorId;
    }

    public static function sendCredit($number, $modelSendCreditRates, $test)
    {

        $countryCode = SendCreditReload::getCountry($modelSendCreditRates->idProduct->currency_dest);

        $access_token = SendCreditReload::getToken();

        $operatorId = SendCreditReload::getOperator($number, $access_token);

        if ($modelSendCreditRates->idProduct->send_value == 0) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://topups.reloadly.com/operators/fx-rate");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            curl_setopt($ch, CURLOPT_POST, true);

            $requestFields = json_encode([
                'operatorId' => $operatorId,
                'amount'     => 1,

            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestFields);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Accept: application/com.reloadly.topups-v1+json",
                "Authorization: Bearer " . $access_token,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response);

            if (isset($result->fxRate)) {

                if (isset($_POST['TransferToMobile']['amountValuesBDT']) && $_POST['TransferToMobile']['amountValuesBDT'] > 0) {
                    $modelSendCreditRates->idProduct->send_value = ((1 / $result->fxRate) * $_POST['TransferToMobile']['amountValuesBDT']) * 1.01;
                } else {
                    $modelSendCreditRates->idProduct->send_value = ((1 / $result->fxRate) * $modelSendCreditRates->idProduct->product) * 1.01;
                }

                $send_value_to_api                           = $modelSendCreditRates->idProduct->send_value;
                $modelSendCreditRates->idProduct->send_value = number_format($modelSendCreditRates->idProduct->send_value, 2);
            } else {
                exit('invalid amount receiveValue');
            }
        } else {
            $send_value_to_api = $modelSendCreditRates->idProduct->send_value;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://topups.reloadly.com/topups");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);

        $requestFields = json_encode([
            'recipientPhone' => [
                'countryCode' => $countryCode,
                'number'      => '+' . $number
            ],
            'senderPhone'    => [
                'countryCode' => 'US',
                'number'      => '+13059547862',
            ],
            'operatorId'     => $operatorId,
            'amount'         => $send_value_to_api,
            //'customIdentifier' => 'Transaction from user ' . Yii::app()->session['username'] . ' ' . time(),

        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestFields);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Accept: application/com.reloadly.topups-v1+json",
            "Authorization: Bearer " . $access_token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        if (isset($result->transactionId) && $result->transactionId > 0 && isset($result->recipientPhone) && $result->recipientPhone == $number) {
            return 'error_txt=Transaction successful=Reload=' . $result->transactionId;
        }
    }
}
