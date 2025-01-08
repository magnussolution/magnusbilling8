<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 *
 */

/*
add the cron to check the BDService transaction status
echo "
 * * * * * php /var/www/html/mbilling/cron.php bdservice
" >> /var/spool/cron/root
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\LoadConfig;
use app\models\TransferToMobile;
use app\models\Refill;

class TransferToMobileController extends CController
{

    public function actionRead($asJson = true, $condition = null)
    {

        $modelTransferToMobile = TransferToMobile::findOne((int) Yii::$app->session['id_user']);

        $methods = [];

        if ($modelTransferToMobile->transfer_international) {
            $methods["Mobile_Credit"] = "Mobile Credit";
        }
        if ($modelTransferToMobile->transfer_flexiload) {
            $methods["Mobile_Money"] = "Mobile Money";
        }
        if ($modelTransferToMobile->transfer_bkash) {
            $methods["Payment"] = "Payment";
        }

        //check the number and methods.
        if (isset($_POST['TransferToMobile']['method'])) {

            if ($_POST['TransferToMobile']['method'] == '') {
                $modelTransferToMobile->addError('method', Yii::t('app', 'Please select a method'));

                $this->render('index', array(
                    'modelTransferToMobile' => $modelTransferToMobile,
                    'methods'               => $methods,
                ));
                return;
            }

            $this->redirect(
                array(
                    'Transfer' . preg_replace('/_/', '', $_POST['TransferToMobile']['method']) . '/index',

                )
            );
        }

        if (count($methods)) {
            $this->render('index', array(
                'modelTransferToMobile' => $modelTransferToMobile,
                'methods'               => $methods,
            ));
        } else {
            echo '<div align=center id="container">';
            echo '<font color=red>Not available any refill method for you</font>';
            echo '</div>';
            exit;
        }
    }

    public function actionPrintRefill()
    {

        if (isset($_GET['id'])) {
            echo '<center>';
            $config    = LoadConfig::getConfig();
            $id_refill = $_GET['id'];

            $modelRefill = Refill::findOne((int) $id_refill, 'id_user = :key', array(':key' => Yii::$app->session['id_user']));

            echo $config['global']['fm_transfer_print_header'] . "<br><br>";

            echo $modelRefill->idUser->company_name . "<br>";
            echo $modelRefill->idUser->address . ', ' . $modelRefill->idUser->city . "<br>";
            echo "Trx ID: " . $modelRefill->id . "<br>";

            echo $modelRefill->date . "<br>";

            $number = explode(" ", $modelRefill->description);

            echo "<br>Cellulare.: " . $number[5] . "<br>";

            if (preg_match('/METER/', strtoupper($modelRefill->description))) {
                $tmp = explode('METER NO.', strtoupper($modelRefill->description));
                $tmp = explode('VIA', strtoupper($tmp[1]));
                echo 'Meter: ' . $tmp[0] . "<br><br>";
            }

            // /SEND CREDIT XOF 2000 - 221771200120- BILL NO. 12345 - DATE 2021-03-01 VIA BILL_ELECTRICITY SENEGAL - EUR 8.00. OR OLD CREDIT 100.0001XOF 2000 Bill_Electricity Senegal

            if (preg_match('/BILL NO/', strtoupper($modelRefill->description))) {
                $tmp  = explode('BILL NO.', strtoupper($modelRefill->description));
                $tmp1 = explode('VIA', strtoupper($tmp[1]));
                $tmp2 = explode('-', strtoupper($tmp1[0]));
                echo 'Bill: ' . $tmp2[0] . "<br>";

                $tmp4 = explode(' DATE ', strtoupper($modelRefill->description));

                echo 'Date: ' . substr($tmp4[1], 0, 10) . "<br><br>";
            }

            $tmp    = explode('EUR ', $modelRefill->description);
            $tmp    = explode('. T', $tmp[1]);
            $amount = $tmp[0];

            $tmp      = explode('via ', $modelRefill->description);
            $operator = strtok($tmp[1], '-');
            $tmp      = explode('Send Credit ', $modelRefill->description);
            $tmp      = explode(' -', $tmp[1]);
            $product  = $tmp[0];

            echo $product . ' ' . $operator . "<br><br>";

            echo "Importo: EUR " . strtok($amount, ' ') . " <br><br>";

            echo $config['global']['fm_transfer_print_footer'] . "<br><br>";

            echo '<td><a href="javascript:window.print()">Print</a></td><br><br>';

            echo '<td><a href="' . $_SERVER['HTTP_REFERER'] . 'index.php/transferToMobile/read">Start new request</a></td>';
            echo '</center>';
        } else {
            echo ' Invalid reffil';
        }
    }
}
