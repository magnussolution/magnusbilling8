<?php

/**
 * Modelo para a tabela "Queue".
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

class  QueueMemberDashBoard extends Model
{
    public $number;

    protected $_module = 'dashboardqueue';
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
        return 'pkg_queue_agent_status';
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
            [['id_queue', 'id_user'], 'integer', 'integerOnly' => true],
            [['agentStatus', 'callId'], 'string', 'max' => 25],
        ];
        return $this->getExtraField($rules);
    }

    /*
     * @return array regras de relacionamento.
     */
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdQueue()
    {
        return $this->hasOne(Queue::className(), ['id' => 'id_queue']);
    }
}