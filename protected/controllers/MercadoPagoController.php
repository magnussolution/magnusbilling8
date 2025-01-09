<?php

/**
 * Url for moip ruturn http://ip/billing/index.php/mercadoPago .
 * https://www.mercadopago.com.br/ipn-notifications
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\UserCreditManager;
use app\components\Util;
use app\models\Methodpay;
use app\models\User;
use MercadoPago\MP;


class MercadoPagoController extends CController
{
    public $config;

    public function actionIndex()
    {
        Yii::error('mercadaoPago' . print_r($_REQUEST, true), 'error');

        require_once 'lib/mercadopago/mercadopago.php';

        $modelMethodpay = Methodpay::find()->where(['payment_method' => 'MercadoPago', 'id_user' => 1, 'active' => 1])->one();

        $mp = new MP($modelMethodpay->username, $modelMethodpay->pagseguro_TOKEN);

        if (! isset($_GET["id"], $_GET["topic"]) || ! ctype_digit($_GET["id"])) {
            http_response_code(400);
            return;
        }

        $topic               = $_GET["topic"];
        $merchant_order_info = null;

        if (isset($_GET["id"])) {
            $payment_info = $mp->get_payment_info($_GET["id"]);

            if ($payment_info["status"] == 200) {

                if (isset($payment_info["response"]['status']) && $payment_info["response"]['status'] == 'approved') {
                    $amount = $payment_info["response"]['transaction_amount'];

                    $identification = Util::getDataFromMethodPay($payment_info["response"]['description']);

                    if (! is_array($identification)) {
                        exit;
                    }
                    $username = $identification['username'];
                    $id_user  = $identification['id_user'];

                    $code        = $payment_info["response"]['id'];
                    $description = "Pagamento confirmado, MERCADOPAGO:" . $code;
                    $modelUser   = User::findOne((int) $id_user);

                    if (isset($modelUser->id)) {
                        Yii::error($modelUser->id . ' ' . $amount . ' ' . $description . ' ' . $code, 'error');
                        UserCreditManager::releaseUserCredit($modelUser->id, $amount, $description, 1, $code);
                        header("HTTP/1.1 200 OK");
                    }
                }
            }
        }
    }
}
