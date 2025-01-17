<?php

/**
 * Modelo para a tabela "Call".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2023 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

namespace app\models;

use Yii;
use app\components\Model;
use yii\db\Exception;

class  CampaignRestrictPhone extends Model
{
    protected $_module = 'campaign';
    /**
     * Retorna a classe estatica da model.
     * @return Prefix classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return nome da tabela.
     */
    public static function tableName()
    {
        return 'pkg_campaign_restrict_phone';
    }

    /**
     * @return nome da(s) chave(s) primaria(s).
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @return array validacao dos campos da model.
     */
    public function rules()
    {
        $rules = [
            [['number'], 'required'],
            [['number'], 'integer', 'integerOnly' => true],
            [['description'], 'string', 'max' => 100],
        ];
        return $this->getExtraField($rules);
    }

    public static function deleteDuplicatedrows()
    {
        $sql = "ALTER TABLE pkg_campaign_restrict_phone DROP INDEX number";
        Yii::$app->db->createCommand($sql)->execute();

        $sql = "ALTER IGNORE TABLE pkg_campaign_restrict_phone ADD UNIQUE (`number`)";
        Yii::$app->db->createCommand($sql)->execute();

        $sql = "ALTER TABLE pkg_campaign_restrict_phone DROP INDEX number";
        Yii::$app->db->createCommand($sql)->execute();

        $sql = "ALTER TABLE  pkg_campaign_restrict_phone ADD INDEX (  `number` )";
        Yii::$app->db->createCommand($sql)->execute();
    }

    public static function insertNumbers($sqlNumbersInsert)
    {
        $sql = 'INSERT IGNORE INTO pkg_campaign_restrict_phone (number)
                            VALUES ' . implode(',', $sqlNumbersInsert) . ';';
        try {
            Yii::$app->db->createCommand($sql)->execute();
            return true;
        } catch (Exception $e) {
            return $e;
        }
    }

    public static function deleteNumbers($sqlNumbersDelete)
    {
        $sql = 'DELETE FROM pkg_campaign_restrict_phone WHERE number IN (' . substr($sqlNumbersDelete, 0, -2) . ');';
        try {
            Yii::$app->db->createCommand($sql)->execute();
            return true;
        } catch (Exception $e) {
            return $e;
        }
    }
}
