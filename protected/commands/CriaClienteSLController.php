<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2023 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

namespace app\commands;

use Yii;
use app\models\User;
use app\models\Methodpay;
use yii\console\ExitCode;
use app\components\ConsoleCommand;

class CriaClienteSLController extends ConsoleCommand
{

    public function actionRun($args = '')
    {
        $modelUser = User::find()->where(['id_sacado_sac' => null])->andWhere(['>', 'id_group', 1])->all();
        foreach ($modelUser as $key => $user) {
            $modelMethodPay = Methodpay::find()->where(['payment_method' => 'SuperLogica', 'active' => 0])->one();
            if (count($modelMethodPay) > 0) {

                $response = $this->saveUserSLCurl(
                    $user,
                    $modelMethodPay->SLAppToken,
                    $modelMethodPay->SLAccessToken
                );

                $user->id_sacado_sac = isset($response[0]->data->id_sacado_sac)
                    ? $response[0]->data->id_sacado_sac
                    : -1;
                $user->save();
            }
        }
        return ExitCode::OK;
    }

    public function saveUserSLCurl($model, $SLAppToken, $SLAccessToken)
    {
        $url = "http://api.superlogica.net:80/v2/financeiro/clientes";

        $params = [
            "ST_NOME_SAC" => $model->firstname . ' ' . $model->lastname,
            "ST_NOMEREF_SAC"              => $model->username,
            "ST_DIAVENCIMENTO_SAC"        => date('d'),
            "ST_CGC_SAC "                 => $model->vat,
            "ST_RG_SAC"                   => $model->doc,
            "ST_CEP_SAC"                  => $model->zipcode,
            "ST_ENDERECO_SAC"             => $model->address,
            "ST_CIDADE_SAC"               => $model->city,
            "ST_ESTADO_SAC"               => $model->state,
            "ST_EMAIL_SAC"                => $model->email,
            "SENHA"                       => $model->password,
            "SENHA_CONFIRMACAO"           => $model->password,
            "ST_TELEFONE_SAC"             => $model->phone,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "app_token: " . $SLAppToken,
            "access_token:" . $SLAccessToken,
        ]);

        $params['identificador'] = '10000' . $model->id;

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = (array) json_decode(curl_exec($ch));

        print_r($response);
        curl_close($ch);
        if (isset($response[0]->status) && $response[0]->status != 200) {

            echo json_encode([
                'success' => false,
                'rows'    => [],
                'errors'  => Yii::t('zii', $response[0]->msg),
            ]);
        }

        return $response;
    }
}
