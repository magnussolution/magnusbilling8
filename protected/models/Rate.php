<?php

/**
 * Modelo para a tabela "Rate".
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
 * 30/07/2012
 */

namespace app\models;

use Yii;
use app\components\Model;
use \Exception;


class  Rate extends Model
{
    protected $_module = 'rate';
    /**
     * Retorna a classe estatica da model.
     * @return Rate classe estatica da model.
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
        return 'pkg_rate';
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
            [['id_plan'], 'required'],
            [['id_plan', 'id_prefix', 'id_trunk_group', 'initblock', 'billingblock', 'package_offer', 'minimal_time_charge '], 'integer', 'integerOnly' => true],
            [['rateinitial', 'connectcharge', 'disconnectcharge', 'additional_grace,status'], 'string', 'max' => 15],
        ];
        return $this->getExtraField($rules);
    }
    /**
     * @return array regras de relacionamento.
     */
    public function getIdTrunkGroup()
    {
        return $this->hasOne(TrunkGroup::class, ['id' => 'id_trunk_group']);
    }

    public function getIdPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'id_plan']);
    }

    public function getIdPrefix()
    {
        return $this->hasOne(Prefix::class, ['id' => 'id_prefix']);
    }

    public static function insertPortabilidadeRates($rates)
    {
        if (count($rates) > 0) {
            $sql = 'INSERT INTO pkg_rate (id_prefix, id_plan, rateinitial,  id_trunk_group, initblock, billingblock,  status) VALUES ' . implode(',', $rates) . ';';
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public static function searchAgentRate($calledstation, $id_plan_agent)
    {
        $sql = "SELECT rateinitial, initblock, billingblock, minimal_time_charge " .
            "FROM pkg_plan " .
            "LEFT JOIN pkg_rate_agent ON pkg_rate_agent.id_plan=pkg_plan.id " .
            "LEFT JOIN pkg_prefix ON pkg_rate_agent.id_prefix=pkg_prefix.id " .
            "WHERE prefix = SUBSTRING(:calledstation,1,length(prefix)) and " .
            "pkg_plan.id= :id_plan_agent ORDER BY LENGTH(prefix) DESC ";

        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(":id_plan_agent", $id_plan_agent, \PDO::PARAM_INT);
        $command->bindValue(":calledstation", $calledstation, \PDO::PARAM_STR);
        return $command->queryAll();
    }

    public static function insertRates($userType, $sqlRate)
    {

        if ($userType == 1) {
            $sqlRate = 'INSERT INTO pkg_rate (id_prefix, id_plan, rateinitial, id_trunk_group, initblock, billingblock, status) VALUES ' . implode(',', $sqlRate) . ';';
        } else {
            $sqlRate = 'INSERT INTO pkg_rate_agent (id_prefix, id_plan, rateinitial,  initblock, billingblock) VALUES ' . implode(',', $sqlRate) . ';';
        }

        try {
            return Yii::$app->db->createCommand($sqlRate)->execute();
        } catch (Exception $e) {
            return $e;
        }
    }
}
