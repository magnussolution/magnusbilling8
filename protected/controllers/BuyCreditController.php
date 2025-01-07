<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\AccessManager;
use app\models\Methodpay;
use app\models\ServicesUse;
use app\models\User;
use app\models\Plan;
use app\models\SLUserSave;


class BuyCreditController extends CController
{

    public function actionMethod()
    {

        if (isset($_GET['l'])) {
            $data           = explode('|', $_GET['l']);
            $user           = $data[0];
            $pass           = $data[1];
            $_GET['amount'] = $data[2];

            $modelSip = AccessManager::checkAccess($user, $pass);

            if (! isset($modelSip->id)) {
                echo 'User or password is invalid';
                exit;
            }
            if (isset($data[3])) {
                $_GET['id_method'] = $data[3];
            } else {
                $methodPay         = Methodpay::model()->find('payment_method = :key', [':key' => 'Paypal']);
                $_GET['id_method'] = $methodPay->id;
            }
            Yii::$app->session['id_user']  = $modelSip->id_user;
            Yii::$app->session['id_plan']  = $modelSip->idUser->id_plan;
            Yii::$app->session['currency'] = $this->config['global']['base_currency'];
        }

        $modelUser = User::model()->findByPk((int) Yii::$app->session['id_user']);
        $modelPlan = Plan::model()->findByPk(Yii::$app->session['id_plan']);

        if (isset($_GET['mobile'])) {

            if (isset($_POST['pay_amount2']) && $_POST['pay_amount2'] > 0) {
                $_POST['pay_amount'] = $_POST['pay_amount2'];
            }

            if (isset($_POST['pay_amount']) && $_POST['pay_amount'] > 0 && isset($_POST['payment_method']) && $_POST['payment_method'] > 0) {
                $_GET['amount']    = $_POST['pay_amount'];
                $_GET['id_method'] = $_POST['payment_method'];
                //continue to the payment method
            } else {

                if (isset($_POST['id_method']) && $_POST['id_method'] > 0) {
                    $modelMethodPay = Methodpay::model()->findByPK((int) $_POST['id_method']);
                    $plan_parts     = explode(' ', $modelPlan->name);
                    if (is_numeric(end($plan_parts))) {
                        $modelMethodPay->min = end($plan_parts);
                    }
                } else {
                    $modelMethodPay = Methodpay::model()->findAll('active = 1');
                }

                $this->render('mobile', [
                    'modelMethodPay' => $modelMethodPay,
                    'modelUser'      => $modelUser,
                    'reference'      => date('YmdHis') . '-' . $modelUser->username . '-' . $modelUser->id,
                ]);
                exit;
            }
        }

        $modelMethodPay = Methodpay::model()->findByPK((int) $_GET['id_method']);
        if (! isset($modelMethodPay->id)) {
            exit;
        }

        $plan_parts = explode(' ', $modelPlan->name);
        if (is_numeric(end($plan_parts))) {
            $modelMethodPay->min = end($plan_parts);
        }

        if ($modelMethodPay->max > 0 && $_GET['amount'] > $modelMethodPay->max || $modelMethodPay->min > 0 && $_GET['amount'] < $modelMethodPay->min) {
            $error = Yii::t('app', 'The minimum amount is') . ' ' . Yii::$app->session['currency'] . ' ' . $modelMethodPay->min;
            $error .= ' ' . Yii::t('app', 'and') . ' ' . Yii::t('app', 'The maximum amount is') . ' ' . Yii::$app->session['currency'] . ' ' . $modelMethodPay->max;
        }

        if (isset($error)) {
            echo "<script>alert('$error')</script>
            <script>window.close();</script>";
            exit;
        }

        if ($modelMethodPay->active == 0 || (isset(Yii::$app->session['id_agent']) && $modelMethodPay->id_user != Yii::$app->session['id_agent'])) {
            exit('invalid option');
        }

        if ($modelMethodPay->payment_method == 'Custom') {
            $url = preg_replace("/\%amount\%/", $_GET['amount'], $modelMethodPay->url);
            foreach ($modelUser as $key => $user) {
                $modelMethodPay->url = preg_replace("/\%$key\%/", $modelUser->$key, $modelMethodPay->url);
            }
            header('Location: ' . $modelMethodPay->url);
        } elseif ($modelMethodPay->payment_method == 'SuperLogica') {
            SLUserSave::criarBoleto($modelMethodPay, $modelUser);
        } else {
            $this->render(strtolower($modelMethodPay->payment_method), [
                'modelMethodPay' => $modelMethodPay,
                'modelUser'      => $modelUser,
                'reference'      => date('YmdHis') . '-' . $modelUser->username . '-' . $modelUser->id,
            ]);
        }
    }

    public function actionPayServiceLink()
    {

        $model    = new ServicesUse();
        $criteria = new CDbCriteria();
        if (isset($_GET['id_service_use'])) {
            $ids = json_decode($_GET['id_service_use']);
            $criteria->addCondition('status = 2');
        } else if (isset($_GET['id_user'])) {
            $criteriaUser = new CDbCriteria();
            $id_user      = (int) $_GET['id_user'];
            $criteriaUser->addCondition('id_user = :id_user');
            //$criteriaUser->addInCondition('reminded', array(2,3));
            $criteriaUser->params[':id_user'] = $id_user;
            $modelServicesUse                 = ServicesUse::model()->findAll($criteriaUser);
            $ids                              = [];
            foreach ($modelServicesUse as $key => $value) {
                $ids[] = $value->id;
            }

            $criteria->addCondition('status = 1');
            //$criteria->addCondition('reminded = 2 OR reminded= 3');
        }

        $criteria->addInCondition('id', $ids);

        $modelServicesUse = ServicesUse::model()->findAll($criteria);

        if (Yii::$app->session['isAdmin']) {
            $total = 0;
            foreach ($modelServicesUse as $key => $value) {
                $total += $modelServicesUse[0]->idServices->price;
                if ($value->id_user != $modelServicesUse[0]->id_user) {
                    $this->render('payservicelink', [
                        'model'   => $model,
                        'message' => 'Your cannot process service payment of diferent users.',
                    ]);
                    exit;
                }
            }

            if (isset($modelServicesUse[0]->id) && $modelServicesUse[0]->idUser->typepaid == 1) {
                $modelServicesUse[0]->idUser->credit = $modelServicesUse[0]->idUser->credit + $modelServicesUse[0]->idUser->creditlimit;
            }

            if (! isset($modelServicesUse[0]->id)) {
                $this->render('payservicelink', [
                    'model'   => $model,
                    'message' => 'This service was paid or canceled.',
                ]);
                exit;
            } else if ($modelServicesUse[0]->idUser->credit >= $total) {
                ServicesProcess::activation([
                    'id_services' => $ids,
                    'id_user'     => (int) $modelServicesUse[0]->id_user,
                    'id_method'   => null,
                ]);
                $this->render('payservicelink', [
                    'model'   => $model,
                    'message' => 'Your services are actived!',
                ]);

                return;
            } else {
                $this->render('payservicelink', [
                    'model'   => $model,
                    'message' => 'User not have enogth credit to pay the services.',
                ]);
                exit;
            }
        }

        if (! is_array($modelServicesUse) || ! isset($modelServicesUse[0]->id)) {
            $this->render('payservicelink', [
                'model'   => $model,
                'message' => 'Your selection not have any service pending.',
            ]);
            exit;
        }

        if ($_POST) {

            $total = explode(" ", $_POST['ServicesUse']['total']);
            $total = floatval($total[1]);

            if (isset($_POST['ServicesUse']['use_credit']) && $_POST['ServicesUse']['use_credit'] == 1) {

                if ($modelServicesUse[0]->idUser->typepaid == 1) {
                    $modelServicesUse[0]->idUser->credit = $modelServicesUse[0]->idUser->credit + $modelServicesUse[0]->idUser->creditlimit;
                }

                if ($modelServicesUse[0]->idUser->credit >= $total) {
                    ServicesProcess::activation([
                        'id_services' => $ids,
                        'id_user'     => (int) Yii::$app->session['id_user'],
                        'id_method'   => (int) $_POST['ServicesUse']['id_method'],
                    ]);
                    $this->render('payservicelink', [
                        'model'   => $model,
                        'message' => 'Your services are actived!',
                    ]);

                    return;
                } else {
                    $total -= $modelServicesUse[0]->idUser->credit;
                }
            }

            if ($_POST['ServicesUse']['id_method'] < 1) {
                $model->addError('id_method', Yii::t('app', 'Group no allow for agent users'));
            } else {

                if (isset($_GET['id_service_use'])) {
                    $link         = $_SERVER['HTTP_REFERER'] . "index.php/buyCredit/payServiceLink/?id_service_use=" . $_GET['id_service_use'];
                    $mail         = new Mail(Mail::$TYPE_SERVICES_PENDING, $modelServicesUse[0]->id_user);
                    $serviceNames = '';
                    foreach ($modelServicesUse as $key => $value) {
                        $serviceNames .= $value->idServices->name . ', ';
                    }

                    $mail->replaceInEmail(Mail::$SERVICE_NAME, $serviceNames);
                    $mail->replaceInEmail(Mail::$SERVICE_PRICE, $total);
                    $mail->replaceInEmail(Mail::$SERVICE_PENDING_URL, $link);
                    try {
                        @$mail->send();
                    } catch (Exception $e) {
                        //error SMTP
                    }
                }

                $modelMethodPay = Methodpay::model()->findByPk((int) $_POST['ServicesUse']['id_method']);
                $total          = $modelMethodPay->payment_method == 'Pagseguro' ? intval($total) : $total;

                $this->redirect(
                    [
                        'buyCredit/method',
                        'amount'    => $total,
                        'id_method' => (int) $_POST['ServicesUse']['id_method'],
                        'id_user'   => $modelServicesUse[0]->id_user,
                    ]
                );
            }
        }

        $modelMethodPay = Methodpay::model()->findAll(
            'id_user = :key AND active = 1',
            [':key' => $modelServicesUse[0]->idUser->id_user]
        );

        if ($modelServicesUse[0]->idUser->typepaid == 1) {
            $modelServicesUse[0]->idUser->credit = $modelServicesUse[0]->idUser->credit + $modelServicesUse[0]->idUser->creditlimit;
        }

        $this->render('payservicelink', [
            'model'            => $model,
            'modelMethodPay'   => $modelMethodPay,
            'modelServicesUse' => $modelServicesUse,
            'currency'         => Yii::$app->session['currency'],
        ]);
    }
}
