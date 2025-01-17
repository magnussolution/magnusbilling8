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


use app\assets\AppAsset;
use yii\bootstrap4\Html;


AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <?php
    function httpPost($url, $params)
    {
        $postData = '';
        //create name value pairs seperated by &
        foreach ($params as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }
        $postData = rtrim($postData, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);

        curl_close($ch);
        return $output;
    }

    $agent = $modelUser->id_user > 0 ? '?id_agent=' . $modelUser->id_user : '';

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';

    $modelUser->doc = preg_replace('/-|\.|\//', '', $modelUser->doc);

    $cpf_cnpj = new ValidaCPFCNPJ($modelUser->doc);

    $params = array(
        "email_loja"          => $modelMethodPay->username,
        "urlRetorno"          => $protocol . $_SERVER['HTTP_HOST'] . '/mbilling/index.php/pagHiper' . $agent,
        "tipoBoleto"          => "boletoA4",
        "vencimentoBoleto"    => "7",
        "id_plataforma"       => $reference,
        "produto_codigo_1"    => $reference,
        "produto_valor_1"     => floatval($_GET['amount']),
        "produto_descricao_1" => "Credito VoIP",
        "produto_qtde_1"      => "1",
        "email"               => $modelUser->email,
        "nome"                => $modelUser->firstname . ' ' . $modelUser->lastname,
        "telefone"            => $modelUser->phone,
        "endereco"            => $modelUser->address,
        "cidade"              => $modelUser->city,
        "estado"              => $modelUser->doc,
        "cep"                 => $modelUser->zipcode,
        "pagamento"           => "pagamento",
    );

    if ($cpf_cnpj->valida_cnpj() == 1) {

        $params['cnpj']         = $modelUser->doc;
        $params['razao_social'] = $modelUser->company_name;
    } else {
        $params['cpf'] = $modelUser->doc;
    }

    echo httpPost("https://www.paghiper.com/checkout/", $params);
    ?>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
<?php die(); ?>