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

use Yii;
use app\components\UserCreditManager;
use app\components\AsteriskAccess;
use app\components\Mail;
use app\models\Sip;
use app\models\User;
use app\models\Services;
use app\models\ServicesUse;
use app\models\Refill;

use Exception;

class ServicesProcess
{

    public static function activation($values)
    {
        $success = false;
        $msg     = 'Error';
        foreach ($values['id_services'] as $key => $id_service) {

            $modelServicesUse = ServicesUse::find(
                'id = :key AND status = 2',
                [
                    ':key' => (int) $id_service,
                ]
            )->one();
            if (! isset($modelServicesUse->id)) {
                continue;
            }

            ServicesProcess::updateUser('activation', $modelServicesUse);

            $modelServicesUse->reservationdate = date('Y-m-d H:i:s');
            $modelServicesUse->status          = 1;
            $modelServicesUse->reminded        = 0;
            $modelServicesUse->month_payed     = 1;
            $modelServicesUse->id_method       = $values['id_method'];
            $modelServicesUse->save();

            $success = true;
            $msg     = 'Service was activated';
        }
        return json_encode([
            'success' => $success,
            'msg'     => $msg,
        ]);
    }

    public static function release($id_services)
    {
        $modelServicesUse = ServicesUse::find((int) $id_services)->one();
        if ($modelServicesUse->status == 1) {

            if ($modelServicesUse->idServices->return_credit == 1) {
                $priceToreturn = ServicesProcess::checkStatus($modelServicesUse);

                if ($priceToreturn > 0) {
                    //expired
                    //have days yet.
                    $modelUser                           = User::find((int) $modelServicesUse->id_user)->one();
                    $modelServicesUse->idServices->price = $priceToreturn;

                    $description              = Yii::t('zii', 'Return credit after cancellation') . '. ' . Yii::t('zii', 'Service') . ' ' . Yii::t('zii', 'name') . ' ' . $modelServicesUse->idServices->name;
                    $modelRefill              = new Refill();
                    $modelRefill->id_user     = $modelServicesUse->id_user;
                    $modelRefill->credit      = $priceToreturn;
                    $modelRefill->description = $description;
                    $modelRefill->payment     = 1;
                    $modelRefill->save();

                    ServicesProcess::updateUser('release', $modelServicesUse);
                }
            }

            $modelServicesUse->releasedate = date('Y-m-d H:i:s');
            $modelServicesUse->status      = 0;
            $modelServicesUse->save();
        } elseif ($modelServicesUse->status == 2) {
            ServicesUse::model()->deleteByPk((int) $id_services);
        }
        echo json_encode([
            'success' => true,
            'msg'     => 'Service was canceled',
        ]);
    }

    public static function buyService($values)
    {
        if ($values['isClient']) {
            $modelUser = User::find((int) $values['id_user'])->one();
            $id_agent  = is_null($modelUser->id_user) ? 1 : $modelUser->id_user;
        }

        $modelServices = Services::find((int) $values['id_services'])->one();

        return [
            'amount' => $modelServices->price,
        ];
    }

    public static function checkStatus($modelServicesUse)
    {
        $data = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime($modelServicesUse->reservationdate)) . " +$modelServicesUse->month_payed month"));

        if ($data > date("Y-m-d")) {

            $month_payed = $modelServicesUse->month_payed - 1;
            //data do ultimo vencimento
            $data = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime($modelServicesUse->reservationdate)) . " +$month_payed month"));

            $secondsUsedThisMonth = time() - strtotime($data);

            $secondsInMonth = 60 * 60 * 24 * date('t');
            $pricePerSecond = $modelServicesUse->idServices->price / $secondsInMonth;

            return $modelServicesUse->idServices->price - ($pricePerSecond * $secondsUsedThisMonth);
        } elseif ($data == date("Y-m-d")) {
            return 0;
            //echo 'vence Hoje';
        } elseif ($data < date("Y-m-d")) {
            //echo 'Expired';
            return -1;
        }
    }

    public static function updateUser($method, $modelServicesUse, $updateUserCredit = true)
    {

        $modelUser = User::find((int) $modelServicesUse->id_user)->one();

        switch ($modelServicesUse->idServices->type) {
            case 'disk_space':
                if ($modelServicesUse->idUser->disk_space < 0 && $method == 'activation') {
                    $modelServicesUse->idServices->disk_space = 0;
                }
                //desativa gravacoes se o usuario ficar com espaço em disco menor que 1
                if (
                    $method != 'activation' &&
                    ($modelServicesUse->idUser->disk_space - $modelServicesUse->idServices->disk_space < 1)
                ) {
                    $modelSip = Sip::model()->find(
                        'id_user = :key',
                        [':key' => $modelServicesUse->id_user]
                    );
                    $modelSip->record_call = 0;
                    $modelSip->save();
                }
                if ($method == 'activation') {
                    $modelUser->disk_space += $modelServicesUse->idServices->disk_space;
                } else {
                    $modelUser->disk_space -= $modelServicesUse->idServices->disk_space;
                }

                break;
            case 'sipAccountLimit':
                Yii::error('sipAccountLimit', 'error');
                if ($modelServicesUse->idUser->sipaccountlimit < 0 && $method == 'activation') {
                    $modelServicesUse->idServices->sipaccountlimit = 0;
                }
                //deleta as contas voip que superam o limite do servico comprado.
                if ($method != 'activation') {
                    $modelSip         = Sip::find('id_user = :key', [':key' => $modelServicesUse->id_user])->all();
                    $totalSipAccounts = isset($modelSip->id);
                    $newLimit         = $modelServicesUse->idUser->sipaccountlimit - $modelServicesUse->idServices->sipaccountlimit;
                    $limitToDelete    = $totalSipAccounts - $newLimit - 1;
                    //deleta as contas voip que superam o limite do servico comprado.
                    if ($limitToDelete > 0) {

                        $sipsToDelete = Sip::find()
                            ->where(['id_user' => $modelServicesUse->id_user])
                            ->orderBy(['id' => SORT_DESC])
                            ->limit($limitToDelete)
                            ->all();
                        foreach ($sipsToDelete as $sip) {
                            $sip->delete();
                        }
                    }
                    AsteriskAccess::instance()->sipReload();
                }

                if ($method == 'activation') {
                    $modelUser->sipaccountlimit += $modelServicesUse->idServices->sipaccountlimit;
                } else {
                    $modelUser->sipaccountlimit -= $modelServicesUse->idServices->sipaccountlimit;
                }

                break;
            case 'calllimit':
                if ($modelServicesUse->idUser->calllimit < 0 && $method == 'activation') {
                    $modelServicesUse->idServices->calllimit = 0;
                }

                if ($method == 'activation') {
                    $modelUser->calllimit += $modelServicesUse->idServices->calllimit;
                } else {
                    $modelUser->calllimit -= $modelServicesUse->idServices->calllimit;
                }

                break;
        }

        $modelUser->save();

        $signal = $method == 'activation' ? '-' : '+';
        $credit = $signal . $modelServicesUse->idServices->price;

        $description = Yii::t('zii', $method) . ' ' . Yii::t('zii', 'Service') . ' ' . $modelServicesUse->idServices->name;

        if ($method == 'activation') {
            $modelRefill          = new Refill();
            $modelRefill->id_user = $modelServicesUse->id_user;
            if (preg_match('/\-\-/', $credit)) {
                $modelRefill->credit = $modelServicesUse->idServices->price * -1;
            } else {
                $modelRefill->credit = $credit;
            }

            $modelRefill->description = $description;
            $modelRefill->payment     = 1;
            $modelRefill->save();
        }

        if ($updateUserCredit == true) {
            //add or remove user credit
            $modelUser = User::find($modelServicesUse->id_user)->one();

            if (preg_match('/\-\-/', $credit)) {
                $modelUser->credit = $modelUser->credit + ($modelServicesUse->idServices->price * -1);
            } else {
                $modelUser->credit = $credit > 0 ? $modelUser->credit + $credit : $modelUser->credit - ($credit * -1);
            }

            $modelUser->saveAttributes(['credit' => $modelUser->credit]);
        }

        if ($method == 'activation') {
            $mail = new Mail(Mail::$TYPE_SERVICES_ACTIVATION, $modelServicesUse->id_user);
        } else {
            $mail = new Mail(Mail::$TYPE_SERVICES_RELEASED, $modelServicesUse->id_user);
        }

        $mail->replaceInEmail(Mail::$SERVICE_NAME, $modelServicesUse->idServices->name);
        $mail->replaceInEmail(Mail::$SERVICE_PRICE, $modelServicesUse->idServices->price);
        try {
            @$mail->send();
        } catch (Exception $e) {
            //error SMTP
        }
    }

    public static function payService($modelServicesUse)
    {
        $modelServicesUse->month_payed++;
        $modelServicesUse->reminded = 0;
        $modelServicesUse->status   = 1;
        $modelServicesUse->save();

        $description = Yii::t('zii', 'Monthly payment Service') . ' ' . $modelServicesUse->idServices->name;
        if ($modelServicesUse->idServices->price > 0) {
            UserCreditManager::releaseUserCredit($modelServicesUse->id_user, '-' . $modelServicesUse->idServices->price, $description);
        } else {
            UserCreditManager::releaseUserCredit($modelServicesUse->id_user, $modelServicesUse->idServices->price, $description);
        }
        $mail = new Mail(Mail::$TYPE_SERVICES_PAID, $modelServicesUse->id_user);
        $mail->replaceInEmail(Mail::$SERVICE_NAME, $modelServicesUse->idServices->name);
        $mail->replaceInEmail(Mail::$SERVICE_PRICE, $modelServicesUse->idServices->price);
        try {
            //@$mail->send();
        } catch (Exception $e) {
            //error SMTP
        }
    }

    public static function releaseService($modelServicesUse)
    {
        $modelServicesUse->status      = 0;
        $modelServicesUse->reminded    = 0;
        $modelServicesUse->releasedate = date('Y-m-d H:i:s');
        $modelServicesUse->save();

        ServicesProcess::updateUser('release', $modelServicesUse, false);
    }
    public static function checkIfServiceToPayAfterRefill($id_user)
    {

        $modelServicesUse             = ServicesUse::find('status = 2 AND id_user = :id_user ', [':id_user' => $id_user])->all();

        foreach ($modelServicesUse as $key => $service) {
            $modelUser = User::find((int) $id_user)->one();

            //se o cliente tem credito para pagar o servico, cobrar imediatamente.
            if ($modelUser->credit >= $service->idServices->price) {

                $service->status = 1;
                $service->month_payed++;
                $service->reminded        = 0;
                $service->reservationdate = date('Y-m-d H:i:s');
                $service->save();
                ServicesProcess::updateUser('activation', $service);
            }
        }
    }
}
