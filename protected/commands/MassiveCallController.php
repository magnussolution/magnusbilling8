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
use CDbCriteria;
use CDbExpression;
use app\models\Plan;
use app\models\User;
use app\models\Trunk;
use app\models\Servers;
use app\models\Campaign;
use yii\console\ExitCode;
use app\models\PhoneNumber;
use app\components\LoadConfig;
use app\models\CampaignReport;
use app\models\TrunkGroupTrunk;
use app\components\Portabilidade;
use app\models\CampaignPhonebook;
use app\components\AsteriskAccess;
use app\components\ConsoleCommand;
use app\components\UserCreditManager;
use app\models\CampaignRestrictPhone;

class MassiveCallController extends ConsoleCommand
{
    public function actionRun($args = '')
    {
        $config         = LoadConfig::getConfig();
        $UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

        $tab_day  = [1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $num_day  = date('N');
        $name_day = $tab_day[$num_day];

        $filter = 'status = :key AND type = :key  AND ' . $name_day . ' = :key AND startingdate <= :key1 AND expirationdate > :key1
                        AND  daily_start_time <= :key2 AND daily_stop_time > :key2 AND frequency > 0';

        $params = [
            ':key'  => 1,
            ':key1' => date('Y-m-d H:i:s'),
            ':key2' => date('H:i:s'),
        ];

        $modelCampaign = Campaign::find()
            ->where($filter, $params)
            ->orderBy(new \yii\db\Expression('RAND()'))
            ->all();

        if ($this->debug >= 1) {
            echo "\nFound " . count($modelCampaign) . " Campaign\n\n";
        }

        foreach ($modelCampaign as $campaign) {
            if ($this->debug >= 1) {
                echo "SEARCH NUMBER IN CAMPAIGN " . $campaign->name . "\n";
            }
            $reportValues = '';

            $id_plan  = $campaign->id_plan > 0 ? $campaign->id_plan : $campaign->idUser->id_plan;
            $id_user  = $campaign->idUser->id;
            $username = $campaign->idUser->username;
            $id_agent = $campaign->idUser->id_user;

            if ($id_agent > 1) {
                $id_plan_agent = $id_plan;
                $modelAgent    = \app\models\User::findOne((int) $id_agent);
                $id_plan       = $modelAgent->id_plan;
            } else {
                $id_plan_agent = 0;
            }

            if (UserCreditManager::checkGlobalCredit($id_user) === false) {
                if ($this->debug >= 1) {
                    echo " USER NO CREDIT FOR CALL " . $username . "\n\n\n";
                }

                continue;
            }

            $modelServers = Servers::find()
                ->where(['status' => 1])
                ->andWhere(['>', 'weight', 0])
                ->andWhere(['or', ['type' => 'mbilling'], ['type' => 'asterisk']])
                ->count();

            $campaign->frequency = $modelServers > 0 ? ceil($campaign->frequency / $modelServers) : $campaign->frequency;

            //get all campaign phonebook
            $modelCampaignPhonebook = CampaignPhonebook::find()
                ->where(['id_campaign' => $campaign->id])
                ->orderBy(new \yii\db\Expression('RAND()'))
                ->all();

            $ids_phone_books = [];
            foreach ($modelCampaignPhonebook as $key => $phonebook) {
                $ids_phone_books[] = $phonebook->id_phonebook;
            }

            $modelPhoneNumber = PhoneNumber::find()
                ->where(['id_phonebook' => $ids_phone_books, 'status' => 1])
                ->andWhere(['<', 'creationdate', date('Y-m-d H:i:s')])
                ->limit($campaign->frequency)
                ->all();

            if ($this->debug >= 1) {
                echo 'Found ' . count($modelPhoneNumber) . ' Numbers in Campaign ' . "\n";
            }

            if (! isset($modelPhoneNumber[0])) {
                if ($this->debug >= 1) {
                    echo "NO PHONE FOR CALL" . "\n\n\n";
                }

                continue;
            }

            if ($campaign->frequency <= 60) {
                //se for menos de 60 por minutos divido 60 pela frequncia e depois somo o resultado para mandar 1 chamada a cada segundos resultante da divisao.
                $sleep = 60 / $campaign->frequency;
            } else {
                //divido a frequencia por 60 e depois mando o resultado em cada segundo.
                $sleep = $campaign->frequency / 60;
            }

            $i         = 0;
            $ids       = [];
            $sleepNext = 1;

            foreach ($modelPhoneNumber as $phone) {
                $ids[] = $phone->id;
            }

            PhoneNumber::updateAll(
                [
                    'status' => 2,
                    'try'    => new \yii\db\Expression('try + 1'),
                ],
                ['id' => $ids]
            );

            foreach ($modelPhoneNumber as $phone) {
                $i++;

                $name_number = $phone->name;
                $destination = $phone->number;

                if ($campaign->restrict_phone == 1) {
                    $modelCampaignRestrictPhone = CampaignRestrictPhone::find()
                        ->where(['number' => $destination])
                        ->one();

                    if (isset($modelCampaignRestrictPhone->id)) {
                        $phone->status = 4;
                        $phone->save();
                        if ($this->debug >= 1) {
                            echo "NUMBER " . $destination . "WAS BLOCKED\n\n\n";
                        }

                        continue;
                    }
                }

                if ($phone->try > 1) {
                    $phone->status = 0;
                    $phone->save();
                    if ($this->debug >= 1) {
                        echo "DISABLE NUMBER  " . $destination . " AFTER TWO TRYING\n\n\n";
                    }

                    continue;
                }

                if (! strlen($destination)) {
                    $phone->status = 0;
                    $phone->save();
                    if ($this->debug >= 1) {
                        echo "DISABLE NUMBER  id =" . $phone->id . " NO DESTINATION \n\n\n";
                    }
                    continue;
                }

                $destination = Portabilidade::getDestination($destination, $id_plan);

                $searchTariff = Plan::searchTariff($id_plan, $destination);

                if (! isset($searchTariff[1][0])) {
                    $phone->status = 0;
                    $phone->save();
                    if ($this->debug >= 1) {
                        echo " NO FOUND RATE TO CALL " . $username . "  DESTINATION $destination \n\n";
                    }

                    continue;
                }

                $searchTariff = $searchTariff[1];

                if ($searchTariff[0]['trunk_group_type'] == 1) {
                    $sql = "SELECT * FROM pkg_trunk_group_trunk WHERE id_trunk_group = " . $searchTariff[0]['id_trunk_group'] . " ORDER BY id ASC";
                } else if ($searchTariff[0]['trunk_group_type'] == 2) {
                    $sql = "SELECT * FROM pkg_trunk_group_trunk WHERE id_trunk_group = " . $searchTariff[0]['id_trunk_group'] . " ORDER BY RAND() ";
                } else if ($searchTariff[0]['trunk_group_type'] == 3) {
                    $sql = "SELECT *, (SELECT buyrate FROM pkg_rate_provider WHERE id_provider = tr.id_provider AND id_prefix = " . $searchTariff[0]['id_prefix'] . " LIMIT 1) AS buyrate  FROM pkg_trunk_group_trunk t  JOIN pkg_trunk tr ON t.id_trunk = tr.id WHERE id_trunk_group = " . $searchTariff[0]['id_trunk_group'] . " ORDER BY buyrate IS NULL , buyrate ";
                }
                $modelTrunkGroupTrunk = TrunkGroupTrunk::findBySql($sql)->all();

                foreach ($modelTrunkGroupTrunk as $key => $trunk) {
                    $modelTrunk = Trunk::findOne((int) $trunk->id_trunk);
                    if ($modelTrunk->status == 0 || $phone->try > 0) {
                        continue;
                    }
                    $idTrunk      = $modelTrunk->id;
                    $trunkcode    = $modelTrunk->trunkcode;
                    $trunkprefix  = $modelTrunk->trunkprefix;
                    $removeprefix = $modelTrunk->removeprefix;
                    $providertech = $modelTrunk->providertech;
                    break;
                }

                if (! isset($idTrunk) || $idTrunk < 1) {
                    continue;
                }

                if (substr($destination, 0, 4) == '1111') {
                    $destination = str_replace(substr($destination, 0, 7), '', $destination);
                }

                $extension = $destination;

                //retiro e adiciono os prefixos do tronco
                if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0 || substr(strtoupper($removeprefix), 0, 1) == 'X') {
                    $destination = substr($destination, strlen($removeprefix));
                }

                $destination = $trunkprefix . $destination;

                if (file_exists(dirname(__FILE__) . '/MassiveCallBeforeDial.php')) {
                    include dirname(__FILE__) . '/MassiveCallBeforeDial.php';
                }

                $dialstr = "$providertech/$trunkcode/$destination";

                // gerar os arquivos .call
                $call = "Action: Originate\n";
                $call = "Channel: " . $dialstr . "\n";
                $call .= "Callerid: " . $campaign->callerid . "\n";
                $call .= "Account:  MC!" . $campaign->name . "!" . $phone->id . "\n";
                //$call .= "MaxRetries: 1\n";
                //$call .= "RetryTime: 100\n";
                //$call .= "WaitTime: 45\n";
                $call .= "Context: billing\n";
                $call .= "Extension: " . $extension . "\n";
                $call .= "Priority: 1\n";
                $call .= "Set:CALLED=" . $extension . "\n";
                $call .= "Set:USERNAME=" . $username . "\n";
                $call .= "Set:IDUSER=" . $id_user . "\n";
                $call .= "Set:PHONENUMBER_ID=" . $phone->id . "\n";
                $call .= "Set:PHONENUMBER_CITY=" . $phone->city . "\n";
                $call .= "Set:CAMPAIGN_ID=" . $campaign->id . "\n";
                $call .= "Set:RATE_ID=" . $searchTariff[0]['id_rate'] . "\n";
                $call .= "Set:TRUNK_ID=" . $idTrunk . "\n";
                $call .= "Set:AGENT_ID=" . $id_agent . "\n";
                $call .= "Set:AGENT_ID_PLAN=" . $id_plan_agent . "\n";
                $call .= "Set:SIPDOMAIN=" . $config['global']['ip_servers'] . "\n";

                if ($this->debug > 1) {
                    echo $call . "\n\n";
                }
                $reportValues .= '(' . $campaign->id . ', ' . $phone->id . ', ' . $id_user . ', ' . $idTrunk . ' , ' . time() . '),';

                AsteriskAccess::generateCallFile($call, $sleepNext);

                if ($campaign->frequency <= 60) {
                    $sleepNext += $sleep;
                } else {
                    //a cada multiplo do resultado, passo para o proximo segundo
                    if (($i % $sleep) == 0) {
                        $sleepNext += 1;
                    }
                }
                $ids[] = $phone->id;
            }
            if (strlen($reportValues)) {
                CampaignReport::insertReport(substr($reportValues, 0, -1));
            }
            echo "Campain " . $campaign->name . " sent " . $i . " calls \n\n";
        }
        return ExitCode::OK;
    }
}
