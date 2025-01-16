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

    require_once 'lib/PlacetoPay/vendor/autoload.php';

    use Yii;
    use app\models\Refill;
    use Dnetix\Redirection\PlacetoPay;

    /**
    * Instanciates the PlacetoPay object providing the login and tranKey, also the url that will be
    * used for the service
    * @return PlacetoPay
    */
    function placetopay($modelMethodPay)
    {

    return new PlacetoPay([
    'login' => $modelMethodPay->P2P_CustomerSiteID,
    'tranKey' => $modelMethodPay->P2P_KeyID,
    'url' => 'https://secure.placetopay.com/redirection/',
    'type' => getenv('P2P_TYPE') ?: PlacetoPay::TP_REST,
    ]);
    }

    $totalAmount = $_GET['amount'];
    $totalAmount = $selectdAmount = preg_replace("/,/", '', $totalAmount);

    if ((isset($_GET['iva']) && $_GET['iva'] == 1) || strlen($modelUser->vat) > 1) {

    if (preg_match("/\+/", $modelUser->vat)) {
    $totalAmount = $totalAmount * ((intval($modelUser->vat) / 100) + 1);
    //$totalAmount = $total - $totalAmount;
    } else {
    $totalAmount = $totalAmount / ((intval($modelUser->vat) / 100) + 1);
    }
    }
    $totalAmount = number_format($totalAmount, 2, '.', '');

    $modelRefill = Refill::find()
    ->where(['id_user' => Yii::$app->session['id_user'], 'payment' => 0])
    ->andWhere(['like', 'description', '%Pendiente%'])
    ->one();
    if (isset($modelRefill->id) > 0) {
    exit('Usted tiene una recarga ' . $modelRefill->description);
    }

    $description = 'Recarga PlaceToPay <font color=blue>Pendiente</font>';

    $modelRefill = new Refill();
    $modelRefill->description = $description;
    $modelRefill->id_user = Yii::$app->session['id_user'];
    $modelRefill->credit = $selectdAmount;
    $modelRefill->payment = 0;
    $modelRefill->save();

    // Creating a random reference for the test
    $reference = $modelRefill->id;
    //echo '
    <pre>';
//print_r($modelUser->getAttributes());
// Request Information
$request = [
    "locale"         => "es_CO",
    "buyer"          => [
        "name"    => $modelUser->firstname,
        "surname" => $modelUser->lastname,
        "email"   => $modelUser->email,

    ],
    "payment"        => [
        "reference"   => $reference,
        "description" => "Credito VOIP para el usuario " . $modelUser->username,
        "amount"      => [
            "currency" => "COP",
            "total"    => $totalAmount,
        ],
    ],
    "expiration"     => date('c', strtotime('+1 hour')),
    "ipAddress"      => "127.0.0.1",
    "userAgent"      => $_SERVER['HTTP_USER_AGENT'],
    "returnUrl"      => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/voziphone/index.php/placetoPay?status=1&ref=' . $reference,
    "cancelUrl"      => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/voziphone/index.php/placetoPay?status=0&ref=' . $reference,
    "skipResult"     => false,
    "noBuyerFill"    => false,
    "captureAddress" => false,
    "paymentMethod"  => null,
];

try {
    $placetopay = placetopay($modelMethodPay);
    $response   = $placetopay->request($request);
    echo '<pre>';
    if ($response->isSuccessful()) {
        //
        // Redirect the client to the processUrl or display it on the JS extension

        $description = 'Recarga PlaceToPay <font color=blue>Pendiente</font>. Referencia: ' . $reference;

        $modelRefill->invoice_number = $response->requestId;
        $modelRefill->description    = $description;
        $modelRefill->save();

        header('Location: ' . $response->processUrl());
    } else {
        // There was some error so check the message
        $response->status()->message();
    }
    var_dump($response->status()->message());
} catch (Exception $e) {
    var_dump($e->getMessage());
}

?>
<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
<?php die(); ?>