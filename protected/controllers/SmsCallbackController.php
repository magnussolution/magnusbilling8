<?php

/**
 * Acoes do modulo "Call".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2025 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\models\Trunk;
use app\models\Callerid;
use app\components\CController;
use app\models\TrunkGroupTrunk;
use app\components\SearchTariff;
use app\components\Portabilidade;
use app\components\AsteriskAccess;

class SmsCallbackController extends CController
{

    public function actionRead($asJson = true, $condition = null)
    {

        if (! isset($_GET['number']) || ! isset($_GET['callerid'])) {
            exit;
        }
        $destination = isset($_GET['number']) ? $_GET['number'] : '';
        $callerid    = isset($_GET['callerid']) ? $_GET['callerid'] : '';
        $date        = date('Y-m-d H:i:s');

        $modelCallerid = Callerid::find()->where(['cid' => $callerid, 'activated' => 1])->one();

        if (! isset($modelCallerid->id)) {
            $error_msg = Yii::t('zii', 'Error : Authentication Error!');
            echo $error_msg;
            exit;
        }

        /*protabilidade*/
        $callerid = Portabilidade::getDestination($callerid, $modelCallerid->idUser->id_plan);

        $SearchTariff = new SearchTariff();
        $callTrunk    = $SearchTariff->find($callerid, $modelCallerid->idUser->id_plan, $modelCallerid->id_user);

        if (substr("$callerid", 0, 4) == 1111) {
            $callerid = str_replace(substr($callerid, 0, 7), "", $callerid);
        }

        if (count($callTrunk) == 0) {
            $error_msg = Yii::t('zii', 'Prefix not found');
            echo $error_msg;
            exit;
        } else {

            if ($searchTariff[0]['trunk_group_type'] == 1) {
                $order = 'id ASC';
            } else if ($searchTariff[0]['trunk_group_type'] == 2) {
                $order = 'RAND()';
            }
            $modelTrunkGroupTrunk = TrunkGroupTrunk::find()
                ->where(['id_trunk_group' => $searchTariff[0]['id_trunk_group']])
                ->orderBy($order)
                ->one();

            $modelTrunk   = Trunk::findOne((int) $modelTrunkGroupTrunk->id_trunk);
            $idTrunk      = $modelTrunk->id;
            $providertech = $modelTrunk->providertech;
            $ipaddress    = $modelTrunk->trunkcode;
            $removeprefix = $modelTrunk->removeprefix;
            $prefix       = $modelTrunk->trunkprefix;

            if (strncmp($callerid, $removeprefix, strlen($removeprefix)) == 0 ||  substr(strtoupper($removeprefix), 0, 1) == 'X') {
                $callerid = substr($callerid, strlen($removeprefix));
            }

            $dialstr = "$providertech/$ipaddress/$prefix$callerid";

            // gerar os arquivos .call
            $call = "Channel: " . $dialstr . "\n";
            $call .= "Callerid: " . $callerid . "\n";
            $call .= "Context: billing\n";
            $call .= "Extension: " . $callerid . "\n";
            $call .= "Priority: 1\n";
            $call .= "Set:CALLED=" . $callerid . "\n";
            $call .= "Set:TARRIFID=" . $callTrunk[0]['idRate'] . "\n";
            $call .= "Set:SELLCOST=" . $callTrunk[0]['rateinitial'] . "\n";
            $call .= "Set:BUYCOST=" . $callTrunk[0]['buyrate'] . "\n";
            $call .= "Set:CIDCALLBACK=1\n";
            $call .= "Set:IDUSER=" . $modelCallerid->id_user . "\n";
            $call .= "Set:IDPREFIX=" . $callTrunk[0]['id_prefix'] . "\n";
            $call .= "Set:IDTRUNK=" . $idTrunk . "\n";
            $call .= "Set:IDPLAN=" . $modelCallerid->idUser->id_plan . "\n";
            $call .= "Set:SECCALL=" . $destination . "\n";
            AsteriskAccess::generateCallFile($call, 5);
        }
    }
}
