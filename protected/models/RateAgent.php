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

class  RateAgent extends Model
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
        return 'pkg_rate_agent';
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
            [['id_plan', 'id_prefix', 'initblock', 'billingblock', 'minimal_time_charge', 'package_offer'], 'integer', 'integerOnly' => true],
            [['rateinitial'], 'string', 'max' => 15],
        ];
        return $this->getExtraField($rules);
    }
    /**
     * @return array regras de relacionamento.
     */
    public function getIdPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'id_plan']);
    }

    public function getIdPrefix()
    {
        return $this->hasOne(Prefix::class, ['id' => 'id_prefix']);
    }

    public function createAgentRates($model, $id_plan)
    {
        $sql = 'INSERT INTO pkg_rate_agent (id_plan , id_prefix,  rateinitial , initblock , billingblock)
                            SELECT ' . $model->id . ', id_prefix, rateinitial, initblock, billingblock FROM pkg_rate WHERE id_plan = ' . $id_plan . '';
        $command = Yii::$app->db->createCommand($sql);
        $command->execute();
    }
}