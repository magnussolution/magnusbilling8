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

class  Alarm extends Model
{
    protected $_module = 'alarm';
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
        return 'pkg_alarm';
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
            [['type', 'amount', 'condition', 'status'], 'required'],
            [['type', 'amount', 'condition', 'status', 'id_plan', 'period'], 'integer', 'integerOnly' => true],
            [['subject'], 'string', 'max' => 200],
            [['message'], 'string', 'max' => 1000],

        ];
        return $this->getExtraField($rules);
    }


    public function getIdPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'id_plan']);
    }


    public function beforeSave($insert)
    {
        if ($this->getIsNewRecord()) {
            $this->creationdate = date('Y-m-d H:i:s');
        }

        return parent::beforeSave($insert);
    }
}
