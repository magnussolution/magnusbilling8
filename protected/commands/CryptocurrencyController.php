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

namespace app\commands;

use Yii;
use app\models\Refill;
use app\models\Methodpay;
use yii\console\ExitCode;
use app\models\Cryptocurrency;
use app\components\ConsoleCommand;
use app\components\UserCreditManager;

class CryptocurrencyController extends ConsoleCommand
{
    public function actionRun($args = '')
    {

        $modelMethodPay = Methodpay::find()->where(['payment_method' => 'cryptocurrency'])->one();
        if (! isset($modelMethodPay->id)) {
            echo 'No method found';
            exit;
        }

        $last_30_minutes     = time() - 1800;
        $modelCryptocurrency = Cryptocurrency::find()
            ->where(['>', 'date', date('Y-m-d')])
            ->andWhere(['status' => 1])
            ->all();

        foreach ($modelCryptocurrency as $key => $payment) {
            $result = '';
            Yii::error(print_r($payment->getAttributes(), true), 'error');

            echo "try get payments\n";
            $command = 'python3.9 /var/www/html/mbilling/protected/commands/crypto.py ' . $modelMethodPay->client_id . ' ' . $modelMethodPay->client_secret . ' ' . $payment->currency . ' ' . $last_30_minutes;
            //Yii::error($command, 'error');
            exec($command, $result);
            if (! isset($result[0])) {
                $command = 'python3 /var/www/html/mbilling/protected/commands/crypto.py ' . $modelMethodPay->client_id . ' ' . $modelMethodPay->client_secret . ' ' . $payment->currency . ' ' . $last_30_minutes;
                exec($command, $result);
            }
            Yii::error(print_r($result, true), 'error');
            $result = implode("\n", $result);
            $result = json_decode($result);

            foreach ($result as $key => $value) {

                if ($value->amount == $payment->amountCrypto) {

                    if (isset($payment->id_user)) {

                        if (Refill::find()->where(['txid' => $value->txId, 'id_user' => $payment->id_user])->count() == 0) {

                            Yii::error('encontrou liberar credit', 'error');
                            $payment->status = 0;
                            $payment->save();
                            $description = 'CriptoCurrency ' . $value->coin . ', txid: ' . $value->txId;
                            Yii::error($description, 'error');
                            echo ($payment->id_user . ' ' . $payment->amount . ' ' . $description . ' ' . $value->txId);
                            Yii::error($payment->id_user . ' ' . $payment->amount . ' ' . $description . ' ' . $value->txId, 'error');
                            Yii::error($description, 'error');
                            UserCreditManager::releaseUserCredit($payment->id_user, $payment->amount, $description, 1, $value->txId);
                        } else {
                            echo "Paymente already released\n";
                        }
                    } else {
                        echo "Receive new deposit in your wallet but not found any refill in your MagnusBilling\n";
                    }
                }
            }
        }

        return ExitCode::OK;
    }
}
