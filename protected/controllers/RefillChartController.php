<?php

/**
 * Acoes do modulo "Refill".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\Refill;

class RefillChartController extends CController
{
    public $attributeOrder = 'date DESC';

    public function actionRead($asJson = true, $condition = null)
    {
        $filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;

        $records = Refill::model()->getRefillChart($filter);

        # envia o json requisitado
        echo json_encode(array(
            $this->nameRoot  => $records,
            $this->nameCount => 25,
        ));
    }
}