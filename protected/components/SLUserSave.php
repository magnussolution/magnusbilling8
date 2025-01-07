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

namespace app\components;

class SLUserSave
{

    public static function saveUserSLCurl($modelUser, $SLAppToken, $SLAccessToken, $showError = true)
    {
        $url    = "http://api.superlogica.net:80/v2/financeiro/clientes";
        $params = [
            "ST_NOME_SAC" => $modelUser->firstname . ' ' . $modelUser->lastname,
            "ST_NOMEREF_SAC"         => $modelUser->username,
            "ST_DIAVENCIMENTO_SAC"   => date('d'),
            "ST_CGC_SAC "            => $modelUser->doc,
            "ST_CEP_SAC"             => $modelUser->zipcode,
            "ST_ENDERECO_SAC"        => $modelUser->address,
            "ST_CIDADE_SAC"          => $modelUser->city,
            "ST_ESTADO_SAC"          => $modelUser->state,
            "ST_EMAIL_SAC"           => $modelUser->email,
            "SENHA"                  => $modelUser->password,
            "SENHA_CONFIRMACAO"      => $modelUser->password,
            "ST_TELEFONE_SAC"        => $modelUser->phone,
            "ST_ENDERECOENTREGA_SAC" => $modelUser->address,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $modelUser->getIsNewRecord() ? "POST" : "PUT");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "app_token: " . $SLAppToken,
            "access_token:" . $SLAccessToken,
        ]);

        if (! $modelUser->getIsNewRecord()) {
            $params['ID_SACADO_SAC'] = $modelUser->id_sacado_sac;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = (array) json_decode(curl_exec($ch));
        curl_close($ch);

        Yii::log(print_r($response, true), 'error');

        if ($response[0]->status != 200 && $showError == true) {

            echo json_encode([
                'success' => false,
                'rows'    => [],
                'errors'  => Yii::t('zii', $response[0]->msg),
            ]);
            exit();
        }

        return $response;
    }

    public static function criarBoleto($methodPay, $modelUser)
    {
        $url = "http://api.superlogica.net:80/v2/financeiro/cobranca";
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "app_token: " . $methodPay->SLAppToken,
            "access_token:" . $methodPay->SLAccessToken,
        ]);

        $SLparams = [
            "ID_SACADO_SAC" => $modelUser->id_sacado_sac,
            "ST_NOMEREF_SAC"             => $modelUser->username,
            "COMPO_RECEBIMENTO"          => [[
                'ID_PRODUTO_PRD'     => $methodPay->SLIdProduto,
                "VL_UNITARIO_PRD"    => $_GET['amount'],
                "NM_QUANTIDADE_COMP" => 1,
            ]],
            "VL_EMITIDO_RECB"            => $_GET['amount'],
            "DT_VENCIMENTO_RECB"         => date("m/d/Y", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))),

        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($SLparams));
        $response = (array) json_decode(curl_exec($ch));
        curl_close($ch);
    }
}
