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
use Exception;

use app\models\Iax;
use app\models\Sip;
use app\models\User;
use app\models\Servers;
use app\models\Campaign;
use yii\console\ExitCode;
use app\models\CallOnLine;
use app\components\LoadConfig;
use app\models\CallOnlineChart;
use app\components\AsteriskAccess;
use app\components\ConsoleCommand;

class CallChartController extends ConsoleCommand
{
    private $totalCalls;
    private $totalUpCalls;
    private $dids;
    private $sips;
    private $sipNames;
    private $didsNumbers;
    public function actionRun($args = '')
    {

        $this->debug = 0;
        $this->isDid();
        $this->isSIPCall();
        for (;;) {
            if (date('i') == 59) {
                break;
            }
            try {
                Servers::updateAll(['status' => 1], ['status' => 2]);
                $calls = AsteriskAccess::getCoreShowCdrChannels();
            } catch (Exception $e) {
                sleep(4);
                continue;
            }

            echo "\n\n\n\nSTART ------\n\n";

            echo "use cdr show \n\n";
            $this->user_cdr_show($calls);
        }
        return ExitCode::OK;
    }

    public function user_cdr_show($calls)
    {
        $modelUserCallShop = User::find()->where(['callshop' => 1])->count();
        if ($modelUserCallShop > 0) {
            $callShopIds       = [];
            $modelUserCallShop = User::find()->where(['callshop' => 1])->all();
            foreach ($modelUserCallShop as $key => $value) {
                $callShopIds[] = $value->id;
            }
        }

        $modelCallOnlineChart         = new CallOnlineChart();
        $modelCallOnlineChart->date   = date('Y-m-d H:i:') . '00';
        $modelCallOnlineChart->answer = 0;
        $modelCallOnlineChart->total  = 0;
        try {
            $modelCallOnlineChart->save();
            $totalUp    = $this->totalUpCalls    = 0;
            $totalCalls = $this->totalCalls = 0;
        } catch (Exception $e) {
            $modelCallOnlineChart = CallOnlineChart::find()->where(['date' => date('Y-m-d H:i:') . '00'])->one();
        }

        if ($modelCallOnlineChart->id > 0) {
            $callOnlineId = $modelCallOnlineChart->id;
        } else {
            $callOnlineId = 0;
        }

        if (count($calls) > 0) {

            $sql = [];

            if ($this->debug > 1) {
                print_r($calls);
            }
            $config         = LoadConfig::getConfig();
            $ip_tech_length = $config['global']['ip_tech_length'];
            $sql            = [];
            foreach ($calls as $key => $call) {
                $modelDid = $modelSip = [];
                $type     = '';
                $channel  = $call[0];

                $status = $call[4];
                if ((preg_match("/Congestion/", $status) || preg_match("/Busy/", $status)) ||
                    (preg_match('/Ring/', $status) && $call[11] > 60)
                ) {
                    AsteriskAccess::instance()->hangupRequest($channel);
                    echo "return after hangup channel\n";
                    continue;
                }
                $uniqueid    = null;
                $trunk       = null;
                $sip_account = $call[1];
                $ndiscado    = $call[2];
                $accountcode = $call[3];
                $codec       = $call[5];
                $des_chan    = $call[6];
                $last_app    = $call[7];
                $cdr         = $call[8];
                $total_time  = $call[9];

                $originate = explode("/", substr($channel, 0, strrpos($channel, "-")));
                $originate = $originate[1];

                if ($last_app == 'Dial' || $last_app == 'Mbilling') {

                    if ($status == 'Ringing') {
                        echo "return because status is Ringing";
                        continue;
                    }

                    if (preg_match('/^MC\!/', $sip_account)) {
                        echo "torpedo\n";
                        $campaingName  = preg_split('/\!/', $call[1]);
                        $modelCampaing = Campaign::find()->where(['name' => $campaingName[1]])->one();
                        $id_user       = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                        $trunk         = "Campaign " . $campaingName[1];
                    } else {

                        //check if is a DID call
                        if (false !== $key = array_search($ndiscado, $this->didsNumbers)) {
                            $modelDid = $this->dids[$key];
                        }
                        //check if is a call to sip account
                        else if (false !== $key = array_search($ndiscado, $this->sipNames)) {
                            $modelSip = $this->sips[$key];
                        }

                        if (isset($modelDid['id'])) {
                            $type    = 'DID';
                            $trunk   = 'DID Call ' . $modelDid['did'];
                            $id_user = isset($modelDid['id_user']) ? $modelDid['id_user'] : null;

                            $didChannel = AsteriskAccess::getCoreShowChannel($channel);
                            if (isset($didChannel['UniqueID'])) {
                                $cdr = time() - intval($didChannel['UniqueID']);
                            }

                            if ($des_chan != '<none>') {
                                if (isset($didChannel['SIPADDHEADER01=P-SipAccount'])) {
                                    $sip_account = $trunk = $didChannel['SIPADDHEADER01=P-SipAccount'];
                                } else if (isset($didChannel['DIALEDPEERNUMBER'])) {
                                    $sip_account = $didChannel['DIALEDPEERNUMBER'];
                                }
                            }
                        } else if (isset($modelSip['id'])) {
                            echo "is a SIP call $ndiscado\n";
                            $type        = 'SIP';
                            $sip_account = $originate;
                            $trunk       = Yii::t('zii', 'SIP Call');
                            $id_user     = $modelSip['id_user'];
                            $is_sip_call = true;
                        } else {

                            //se é autenticado por techprefix

                            //try get user
                            if (preg_match('/^SIP\/sipproxy\-/', $channel)) {
                                if (! strlen($sip_account)) {
                                    $sip_account = $call[1] = $call[3];
                                }
                                if (false !== $key = array_search($sip_account, $this->sipNames)) {
                                    $modelSip = Sip::find()->where(['name' => $this->sips[$key]['name']])->one();
                                } else {
                                    continue;
                                }
                            } else if (strlen($ndiscado) > 15) {
                                $tech     = substr($ndiscado, 0, $ip_tech_length);
                                $modelSip = Sip::find()->where(['techprefix' => $tech])->andWhere(['!=', 'host', 'dynamic'])->one();
                            }

                            if (! isset($modelSip->name)) {

                                if ($status == 'Ring') {
                                    $sip_account = $originate;
                                }
                                if (strlen($sip_account) > 3) {
                                    //echo "check per sip_account $originate\n";
                                    if (false !== $key = array_search($originate, $this->sipNames)) {
                                        $modelSip = Sip::find()->where(['name' => $this->sips[$key]['name']])->one();
                                    }
                                } else if (strlen($accountcode)) {
                                    //echo "check per accountcode $accountcode\n";
                                    $modelSip = Sip::find()->where(['accountcode' => $accountcode])->one();
                                }

                                if (! isset($modelSip->name)) {

                                    if (preg_match('/^IAX/', strtoupper($channel))) {
                                        $modelSip = Iax::find()->where(['name' => $originate])->one();
                                    } else {
                                        //check if is via IP from proxy
                                        $callProxy = AsteriskAccess::getCoreShowChannel($channel, null, $call['server']);
                                        $modelSip  = Sip::find()->where(['host' => $callProxy['X-AUTH-IP']])->one();
                                    }
                                }
                            }

                            $trunk = isset($call[6]) ? $call[6] : 0;

                            if (preg_match("/\&/", $trunk)) {
                                $trunk = preg_split("/\&/", $trunk);
                                $trunk = explode("/", $trunk[0]);
                            } else if (preg_match("/@/", $trunk)) {
                                $trunk    = explode("@", $trunk);
                                $trunk    = explode(",", $trunk[1]);
                                $trunk[1] = $trunk[0];
                            } else {
                                $trunk = explode("/", substr($trunk, 0, strrpos($trunk, "-")));
                            }

                            $type        = 'pstn';
                            $trunk       = isset($trunk[1]) ? $trunk[1] : 0;
                            $id_user     = $modelSip['id_user'];
                            $sip_account = $modelSip['name'];
                        }

                        if ($type == '') {
                            echo "not fount the type call \n";
                            continue;
                        }
                    }
                } elseif ($last_app == 'AGI' || $last_app == 'AppDial2') {
                    if (preg_match('/^MC\!/', $call[1])) {

                        //torpedo
                        $campaingName = preg_split('/\!/', $call[1]);

                        $modelCampaing = Campaign::find()->where(['name' => $campaingName[1]])->one();

                        $id_user = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                        $trunk   = "Campaign " . $campaingName[1];
                    } else {
                        //check if is a DID number
                        //DID call ivr   -> SIP/addphone-000000|           |9999999999    |           |Up|(g729)|<none>             |AGI  |4|5
                        if (false !== $key = array_search($call[2], $this->didsNumbers)) {
                            $modelDid = $this->dids[$key];
                        }
                        if (isset($modelDid['id'])) {

                            $id_user = $modelDid['id_user'];

                            switch ($modelDid['voip_call']) {
                                case 2:
                                    $trunk = $originate . ' IVR';
                                    break;
                                case 3:
                                    $trunk = $originate . ' CallingCard';
                                    break;
                                case 4:
                                    $trunk = $originate . ' portalDeVoz';
                                    break;
                                case 4:
                                    $trunk = $originate . ' CID Callback';
                                    break;
                                case 5:
                                    $trunk = $originate . ' CID Callback';
                                    break;
                                case 6:
                                    $trunk = $originate . ' 0800 Callback';
                                    break;
                                default:
                                    $trunk = $originate . ' DID Call';
                                    break;
                            }
                        } else {
                            $id_user = 'NULL';
                        }
                    }
                } elseif ($last_app == 'Queue') {

                    if (preg_match('/^MC\!/', $call[1])) {
                        //torpedo
                        $campaingName = preg_split('/\!/', $call[1]);

                        $modelCampaing = Campaign::find()->where(['name' => $campaingName[1]])->one();

                        $id_user  = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                        $trunk    = "Campaign " . $campaingName[1];
                        $uniqueid = $campaingName[2];
                    } else {

                        //check if is a DID number
                        if (false !== $key = array_search($call[2], $this->didsNumbers)) {
                            $modelDid = $this->dids[$key];
                        }

                        if (isset($modelDid['id'])) {
                            $id_user = $modelDid['id_user'];
                            $trunk   = $ndiscado . ' Queue ';
                            if ($status == 'Up') {
                                $callQueue = AsteriskAccess::getCoreShowChannel($channel, null, $call['server']);
                                if (isset($callQueue['UniqueID'])) {
                                    $cdr = time() - intval($callQueue['UniqueID']);
                                }
                                if (isset($callQueue['MEMBERNAME'])) {
                                    $sip_account = substr($callQueue['MEMBERNAME'], 4);
                                }
                            }
                        } else {
                            $id_user = 'NULL';
                        }
                    }
                } else {
                    echo "continue because last_app is not valid $last_app\n";
                    continue;
                }

                if (! is_numeric($id_user)) {

                    echo "continue because not found id_user\n";
                    continue;
                }

                if (! is_numeric($id_user) || ! is_numeric($cdr)) {
                    echo "continue because not foun id_user or cdr\n";
                    continue;
                }

                if (preg_match("/Up/", $status)) {
                    $totalUp++;
                }
                $totalCalls++;

                $sql[] = "(NULL,'" . $uniqueid . "', '$sip_account', $id_user, '$channel', '" . mb_convert_encoding($trunk, 'UTF-8', 'ISO-8859-1') . "', '$ndiscado', '" . preg_replace('/\(|\)/', '', $codec) . "', '$status', '$cdr', 'no','no', '" . $call['server'] . "')";

                if (is_array($callShopIds)) {
                    if (in_array($id_user, $callShopIds)) {

                        if (! isset($modelSip->id)) {
                            $modelSip = Sip::find()->where(['name' => $sip_account])->one();

                            if (isset($modelSip->id)) {
                                $modelSip->status         = 3;
                                $modelSip->callshopnumber = $ndiscado;
                                $modelSip->callshoptime   = $cdr;
                                $modelSip->save();
                                $modelSip = null;
                            }
                        }
                    }
                }
            }

            if ($totalUp > $this->totalUpCalls) {
                $this->totalUpCalls = $totalUp;
                echo "totalUp é > total1\n";
            }

            if ($totalCalls > $this->totalCalls) {
                $this->totalCalls = $totalCalls;
            }

            $modelCallOnlineChart->answer = $this->totalUpCalls;
            $modelCallOnlineChart->total  = $this->totalCalls;
            $modelCallOnlineChart->save();

            echo 'totalUp = ' . $totalUp . ' -> totalCalls = ' . $totalCalls . "\n";
            echo 'this->totalUpCalls = ' . $this->totalUpCalls . ' -> this->totalCalls = ' . $this->totalCalls . "\n";
            CallOnLine::deleteAll();

            if (count($sql) > 0) {

                $result = CallOnLine::insertCalls($sql);
                if ($this->debug > 1) {
                    print_r($result);
                }
            }
        } else {
            CallOnLine::deleteAll();
        }
        sleep(4);
    }

    private function isDid()
    {
        $sql               = "SELECT did, id, id_user FROM pkg_did WHERE reserved = 1";
        $this->dids        = Yii::$app->db->createCommand($sql)->queryAll();
        $this->didsNumbers = isset($this->dids[0]) ? array_column($this->dids, 'did') : [];
    }
    private function isSIPCall()
    {
        $sql            = "SELECT name, id_user FROM pkg_sip";
        $this->sips     = Yii::$app->db->createCommand($sql)->queryAll();
        $this->sipNames = array_column($this->sips, 'name');
    }
}
