<?php

/**
 * Url for paypal ruturn http://http://billing3.cwiz.com.br/mbilling/index.php/superLogica .
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\UserCreditManager;
use app\models\Methodpay;
use app\models\User;
use app\models\Boleto;

class SuperLogicaController extends CController
{

    public function actionIndex()
    {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        // specify how many levels of call stack should be shown in each log message
        defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

        Yii::error(print_r($_REQUEST, true), 'error');
        echo json_encode(["status" => 200]);

        $modelMethodpay = Methodpay::find()->where(['payment_method' => 'SuperLogica'])->one();

        $sql             = "SELECT * FROM pkg_method_pay WHERE payment_method = 'SuperLogica'";
        $result          = Yii::$app->db->createCommand($sql)->queryAll();
        $pppToken        = $modelMethodpay->SLAppToken;
        $accessToken     = $modelMethodpay->SLAccessToken;
        $secret          = $modelMethodpay->SLSecret;
        $validationtoken = $modelMethodpay->SLvalidationtoken;

        if (! isset($_POST['validationtoken']) || $validationtoken != $_POST['validationtoken']) {
            Yii::error('invalid token', 'info');
            exit();
        }

        if (! isset($_POST['data']['id_recebimento_recb'])) {
            Yii::error('No POST', 'info');
            exit();
        }

        if (! isset($_POST['data']['id_sacado_sac'])) {
            Yii::error('No exists id sacado', 'info');
            exit();
        }
        $id_recebimento_recb = $_POST['data']['id_recebimento_recb'];
        $id_sacado_sac       = $_POST['data']['id_sacado_sac'];

        $modelUser = User::find()->where(['id_sacado_sac' => $id_sacado_sac])->one();
        if (! isset($modelUser->id)) {
            exit;
        }
    }
}
