<?php

/**
 * Class to send credit to mobile via SendCreditOrange2
 *
 * MagnusBilling <info@magnusbilling.com>
 * 08/07/2018
 */

namespace app\components;

class SendCreditOrange2
{

    public function billElectricity($post, $modelSendCreditRates, $test)
    {
        $date = date_create($post['creationdate']);

        switch ($_POST['TransferToMobile']['country']) {
            case 'Benin':
                $CC = 'BJ';
                $id = 1;
                break;
            case 'Burkina Faso':
                $CC = 'BF';
                $id = 2;
                break;
            case 'Ivory Coast':
                $CC = 'CI';
                $id = 1;
                break;
            case 'Gambia':
                $CC = 'GM';
                $id = 1;
                break;
            case 'Ghana':
                $CC = 'GH';
                $id = 1;
                break;
            case 'Guinea':
                $CC = 'GN';
                $id = 1;
                break;
            case 'Guinea-Bissau':
                $CC = 'GW';
                $id = 1;
                break;
            case 'Mauritania':
                $CC = 'MR';
                $id = 1;
                break;
            case 'Morocco':
                $CC = 'MA';
                $id = 1;
                break;
            case 'Sierra Leone':
                $CC = 'SL';
                $id = 1;
                break;
            case 'Togo':
                $CC = 'TG';
                $id = 1;
                break;
            case 'Mali':
                $CC = 'ML';
                $id = 1;
                break;
            case 'Nigeria':
                $CC = 'NG';
                $id = 1;
                break;
            case 'Senegal':
                $CC = 'SN';
                $id = 1;
                break;
            default:
                $CC = 'SN';
                $id = 1;
                return false;
                break;
        }

        $order = [
            "beneficiary"   => [
                'mobile' => '+' . $post['phone'],
                "name"   => "ABC",

            ],
            "type"          => "bill",
            "billOrderData" => [
                "billType" => [
                    "id" => $id,

                ],
                'data'     => "{\"number\":\"" . $post['number'] . "\",\"date\":\"" . date_format($date, "d/m/Y") . "\",\"distrubutionCode\":\"" . $_POST['TransferToMobile']['zipcode'] . "\"}",
            ],
            "targetTotal"   => $post['bill_amount'],

        ];

        return SendCreditOrange2::sendOrder($order);
    }

    public static function sendCredit($number, $modelSendCreditRates, $test)
    {

        if (! isset($_POST['TransferToMobile']['meter'])) {
            $order = SendCreditOrange2::sendPayment($number, $modelSendCreditRates, $test);
        } else {

            if (preg_match('/-/', $modelSendCreditRates->idProduct->product)) {
                $modelSendCreditRates->idProduct->product = $_POST['TransferToMobile']['amountValuesBDT'];
            }

            $order = [
                "type"        => "cashpower",
                "targetTotal" => $modelSendCreditRates->idProduct->product,
                "beneficiary" => [
                    'mobile' => '+' . $number,
                    "name"   => "ABC",

                ],
                "meterId"     => $_POST['TransferToMobile']['meter'],
            ];
        }
        return SendCreditOrange2::sendOrder($order);
    }

    public function sendOrder($order = '')
    {

        $config = LoadConfig::getConfig();

        $username = $config['global']['Orange2_username'];
        $password = $config['global']['Orange2_password'];
        $test     = $config['global']['Orange2_test'];

        if ($test == 1) {
            $url = 'https://qa.baluwo.com/rest/v1/external/transaction';
        } else {
            $url = 'https://app.baluwo.com/rest/v1/external/transaction';
        }

        $data = [
            "buyer"    => [
                "mobile" => "+39370000000",
                "name"   => "INVIONET",
            ],
            "clientId" => 12,
            "orders"   => [$order],

        ];

        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
        ]);
        $output = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        $output = json_decode($output, JSON_UNESCAPED_SLASHES);
        if (isset($output['id'])) {
            $output = (object) $output;
        }

        if (! isset($output->id) || ! is_numeric($output->id)) {

            return 'error_txt=' . print_r($output['message'], true);
        }

        $transaction_id = $output->id;

        if ($test == 1) {
            $url = 'https://qa.baluwo.com/rest/v1/external/transaction/pay/' . $transaction_id;
        } else {
            $url = 'https://app.baluwo.com/rest/v1/external/transaction/pay/' . $transaction_id;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: 0',
        ]);
        $output = curl_exec($ch);

        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (preg_match('/204/', $info)) {
            return 'error_txt=Transaction successful=Orange2=' . $transaction_id;
        } else {
            return 'error_txt=Error' . print_r($output, true);
        }
    }

    public static function checkMetric($metric = '', $country = '')
    {

        $config = LoadConfig::getConfig();

        $username = $config['global']['Orange2_username'];
        $password = $config['global']['Orange2_password'];
        $test     = $config['global']['Orange2_test'];

        $test = 1;

        if (strlen($country)) {
            $_POST['TransferToMobile']['country'] = $country;
        }

        switch ($_POST['TransferToMobile']['country']) {
            case 'Benin':
                $CC = 'BJ';
                break;
            case 'Burkina Faso':
                $CC = 'BF';
                break;
            case 'Ivory Coast':
                $CC = 'CI';
                break;
            case 'Gambia':
                $CC = 'GM';
                break;
            case 'Ghana':
                $CC = 'GH';
                break;
            case 'Guinea':
                $CC = 'GN';
                break;
            case 'Guinea-Bissau':
                $CC = 'GW';
                break;
            case 'Mauritania':
                $CC = 'MR';
                break;
            case 'Morocco':
                $CC = 'MA';
                break;
            case 'Sierra Leone':
                $CC = 'SL';
                break;
            case 'Togo':
                $CC = 'TG';
                break;
            case 'Mali':
                $CC = 'ML';
                break;
            case 'Nigeria':
                $CC = 'NG';
                break;
            case 'Senegal':
                $CC = 'SN';
                break;
            default:
                return false;
                break;
        }

        if ($test == 1) {
            $url = 'https://qa.baluwo.com/rest/v1/external/cashpower/check/' . $CC . '/' . $metric;
        } else {
            $url = 'https://app.baluwo.com/rest/v1/external/cashpower/check/' . $CC . '/' . $metric;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 7);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $output = curl_exec($ch);

        curl_close($ch);
        if (preg_match('/Meter not found/', $output)) {
            return false;
        } else if (preg_match('/Unknown/', $output)) {
            return 'Unknown';
        } else if (preg_match('/customerName/', $output)) {
            $output = explode('{"customerName":"', $output);

            return substr($output[1], 0, -2);
        } else {
            return 'Unknown';
        }
    }

    public function sendPayment($number, $modelSendCreditRates, $test)
    {

        $modelSendCreditProducts = SendCreditProducts::model()->findOne((int) $modelSendCreditRates->id_product);

        if (preg_match('/Bundle/', $modelSendCreditProducts->operator_name)) {
            $type = 'airdata';
        } else {
            $type = 'airtime';
        }

        if (preg_match('/\-/', $modelSendCreditRates->idProduct->product)) {

            $order = [
                "type"        => $type,
                "targetTotal" => $_POST['TransferToMobile']['amountValuesBDT'],
                "beneficiary" => [
                    'mobile' => '+' . $number,
                    "name"   => "ABC",

                ]
            ];
        } else {
            $order = [
                "type"        => $type,
                "targetTotal" => $modelSendCreditRates->idProduct->product,
                "beneficiary" => [
                    'mobile' => '+' . $number,
                    "name"   => "ABC",

                ]
            ];
        }

        return $order;
    }
}
