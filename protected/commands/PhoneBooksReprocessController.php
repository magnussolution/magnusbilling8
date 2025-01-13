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
use app\models\Campaign;
use yii\console\ExitCode;
use app\models\PhoneNumber;
use app\models\CampaignPhonebook;
use app\components\ConsoleCommand;

class PhoneBooksReprocessController extends ConsoleCommand
{
    public function actionRun($args = '')
    {
        $modelCampaign = Campaign::find()->where(['status' => 1, 'auto_reprocess' => 1])->all();
        foreach ($modelCampaign as $key => $campaign) {

            $modelCampaignPhonebook = CampaignPhonebook::find()->where(['id_campaign' => $campaign->id])->all();

            if (! isset($modelCampaignPhonebook[0]->id_phonebook)) {
                continue;
            }

            $ids_phone_books = '';
            foreach ($modelCampaignPhonebook as $key => $phonebook) {
                $ids_phone_books .= $phonebook->id_phonebook . ',';
            }
            $ids_phone_books = substr($ids_phone_books, 0, -1);

            $sql              = "SELECT * FROM `pkg_phonenumber` WHERE status = 1 AND id_phonebook IN ($ids_phone_books)";
            $modelPhoneNumber = PhoneNumber::findBySql($sql)->one();
            if (isset($modelPhoneNumber->id)) {
                continue;
            }
            echo "REPROCESSAR IDS " . $ids_phone_books . "\n";

            $ids_phone_books = explode(',', $ids_phone_books);

            PhoneNumber::updateAll(
                ['status' => 1, 'try' => 0],
                ['and', ['in', 'id_phonebook', $ids_phone_books], ['status' => 2]]
            );
        }
        return ExitCode::OK;
    }
}
