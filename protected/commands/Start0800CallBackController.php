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
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */
//not check credit and send call to any number, active or inactive
namespace app\commands;

use Yii;
use app\models\Sip;
use app\models\User;
use app\models\Trunk;
use app\models\CallBack;
use yii\console\ExitCode;
use app\models\Diddestination;
use app\models\TrunkGroupTrunk;
use app\components\SearchTariff;
use app\components\AsteriskAccess;
use app\components\ConsoleCommand;

class Start0800CallBackController extends ConsoleCommand
{
    public function actionRun($args = '')
    {
        ini_set("max_execution_time", "900");

        for (;;) {

            if (date('s') == 58) {
                exit;
            }
            sleep(1);

            $modelCallBack = CallBack::find()->where(['status' => [1, 2, 4]])->all();

            foreach ($modelCallBack as $callback) {

                echo "Callback to number " . $callback->exten . "\n";
                //

                $status = $callback->status;
                echo $status . "\n";

                //print_r($callback->getAttributes());

                $modelDiddestination = Diddestination::find()->where(['id_did' => $callback->id_did])->one();

                if (!isset($modelDiddestination->id)) {
                    CallBack::deleteAll(['id' => $callback->id]);
                    continue;
                }

                $modelDid = $modelDiddestination->idDid;

                //esperar 60 segundos antes de tentar ligar para o cliente.
                if ($status == 1 && $callback->entry_time > date('Y-m-d H:i:s', strtotime('-1 minutes'))) {
                    echo "esperar 60 segundos antes de tentar ligar para o cliente. " . $callback->entry_time . ' ' . date('Y-m-d H:i:s', strtotime('-1 minutes')) . "\n";
                    continue;
                }

                //

                $voltarChamar = time() - ($modelDid->cbr_time_try * 60);
                $voltarChamar = date('Y-m-d H:i:s', $voltarChamar);

                if ($callback->status == 2 && $callback->num_attempt < $modelDid->cbr_total_try && $callback->last_attempt_time < $voltarChamar) {
                    echo " reative number because status = 2 and last_attempt_time > than " . $modelDid->cbr_time_try . "\n";
                    $status = 1;
                }

                if ($callback->status == 4) {
                    $work = $this->checkIVRSchedule($modelDid);
                    if ($work == 'open') {
                        $status                = 1;
                        $callback->status      = 1;
                        $callback->sessiontime = 0;
                        $callback->save();
                    }
                }

                if ($status != 1) {
                    continue;
                }

                $destination = $callback->exten;

                $modelUser = $modelDiddestination->idUser;

                //PEGA O PREÇO DE VENDA DO AGENT
                if ($modelUser->id_user > 1) {
                    $modelUserAgent = User::findOne((int) $modelUser->id_user);
                    $id_plan        = $modelUserAgent->id_plan;
                } else {
                    $id_plan = $modelDiddestination->idUser->id_plan;
                }

                $searchTariff = new SearchTariff();
                $searchTariff = $searchTariff->find($destination, $modelUser->id_plan, $modelUser->id);

                if (!isset($searchTariff[0])) {
                    $callback->status = 4;
                    $callback->save();
                    if ($this->debug >= 1) {
                        echo " NO FOUND RATE TO CALL " . $modelUser->username . "  DESTINATION $destination \n\n";
                    }

                    continue;
                }

                if ($searchTariff[0]['trunk_group_type'] == 1) {
                    $order = 'id ASC';
                } else if ($searchTariff[0]['trunk_group_type'] == 2) {
                    $order = 'RAND()';
                }

                $modelTrunkGroupTrunk = TrunkGroupTrunk::find()
                    ->where(['id_trunk_group' => $searchTariff[0]['id_trunk_group']])
                    ->orderBy($order)
                    ->one();

                if (substr("$destination", 0, 4) == 1111) {
                    $destination = str_replace(substr($destination, 0, 7), "", $destination);
                }

                $credit = $modelDiddestination->idUser->typepaid == 1
                    ? $modelDiddestination->idUser->credit + $modelDiddestination->idUser->creditlimit
                    : $modelDiddestination->idUser->credit;

                if ($credit > 0) {
                    $modelSip = Sip::find()->where(['id_user' => $modelUser->id])->one();
                    $callerid = isset($modelSip->id) ? $modelSip->callerid : $callback['exten'];
                    $modelTrunk   = Trunk::findOne((int) $modelTrunkGroupTrunk->id_trunk);
                    $idTrunk      = $modelTrunk->id;
                    $providertech = $modelTrunk->providertech;
                    $ipaddress    = $modelTrunk->trunkcode;
                    $removeprefix = $modelTrunk->removeprefix;
                    $prefix       = $modelTrunk->trunkprefix;

                    if ($modelTrunk->cnl == 1) {
                        if (substr($destination, 4, 1) == 9) {
                            if (substr($destination, 2, 2) == substr($callerid, 0, 2)) {
                                $removeprefix = "XXXX";
                                $prefix       = "";
                            }
                        } else if (strlen($modelSip->cnl) > 1) {
                            $sql      = "SELECT zone FROM pkg_cadup a JOIN pkg_provider_cnl b ON a.cnl = b.cnl WHERE prefix = '" . substr($destination, 0, 8) . "' AND id_provider = " . $modelTrunk->id_provider . " LIMIT 1";
                            $modelCNL = $agi->query($sql)->fetch(PDO::FETCH_OBJ);

                            if (isset($modelCNL->zone) && $modelCNL->zone == $modelSip->cnl) {
                                $removeprefix = "XXXX";
                                $prefix       = "";
                            }
                        }
                    }

                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0 || substr(strtoupper($removeprefix), 0, 1) == 'X') {
                        $destination = substr($destination, strlen($removeprefix));
                    }

                    $dialstr = "$providertech/$ipaddress/$prefix$destination";

                    // gerar os arquivos .call
                    $call = "Channel: " . $dialstr . "\n";
                    $call .= "Callerid: " . $callerid . "\n";
                    $call .= "Context: billing\n";
                    $call .= "Extension: " . $modelDiddestination->idDid->did . "\n";
                    $call .= "Priority: 1\n";
                    $call .= "Priority: 1\n";
                    $call .= "Set:CALLED=" . $destination . "\n";
                    $call .= "Set:TARRIFID=" . $searchTariff[0]['id_rate'] . "\n";
                    $call .= "Set:SELLCOST=" . $searchTariff[0]['rateinitial'] . "\n";
                    $call .= "Set:SELLINITBLOCK=" . $searchTariff[0]['initblock'] . "\n";
                    $call .= "Set:SELLINCREMENT=" . $searchTariff[0]['billingblock'] . "\n";
                    $call .= "Set:IDUSER=" . $modelDiddestination->id_user . "\n";
                    $call .= "Set:IDPREFIX=" . $searchTariff[0]['id_prefix'] . "\n";
                    $call .= "Set:IDTRUNK=" . $idTrunk . "\n";
                    $call .= "Set:IDPLAN=" . $modelDiddestination->idUser->id_plan . "\n";
                    $call .= "Set:IDCALLBACK=" . $callback->id . "\n";
                    $call .= "Set:ISFROMCALLBACKPRO=1\n";
                    AsteriskAccess::generateCallFile($call, 1);

                    echo $call;
                    $callback->num_attempt++;
                    $callback->last_attempt_time = date('Y-m-d H:i:s');
                    $callback->status            = 2;
                    $callback->save();
                    $modelError = $callback->getErrors();
                    if (count($modelError)) {
                        print_r($modelError);
                    }
                }
            }
        }
        return ExitCode::OK;
    }

    public function checkIVRSchedule($model)
    {
        $weekDay = date('D');

        switch ($weekDay) {
            case 'Sun':
                $weekDay = $model->{'TimeOfDay_sun'};
                break;
            case 'Sat':
                $weekDay = $model->{'TimeOfDay_sat'};
                break;
            default:
                $weekDay = $model->{'TimeOfDay_monFri'};
                break;
        }

        $hours   = date('H');
        $minutes = date('i');
        $now     = ($hours * 60) + $minutes;

        $intervals = preg_split("/\|/", $weekDay);

        foreach ($intervals as $key => $interval) {
            $hours = explode('-', $interval);

            $start = $hours[0];
            $end   = $hours[1];

            #convert start hour to minutes
            $hourInterval = explode(':', $start);
            $starthour    = $hourInterval[0] * 60;
            $start        = $starthour + $hourInterval[1];

            #convert end hour to minutes
            $hourInterval = explode(':', $end);
            $starthour    = $hourInterval[0] * 60;
            $end          = $starthour + $hourInterval[1];

            if ($now >= $start && $now <= $end) {
                return "open";
            }
        }
        return "closed";
    }
}
