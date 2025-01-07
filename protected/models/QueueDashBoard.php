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

class  QueueDashBoard extends Model
{

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
        return 'pkg_queue_status';
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
            [['id_queue', 'id_agent', 'priority'], 'integer', 'integerOnly' => true],
            [['keyPressed', 'holdtime', 'originalPosition', 'position'], 'string', 'max' => 11],
            [['queue', 'timestamp', 'queue_name'], 'string', 'max' => 25],
            [['status'], 'string', 'max' => 30],
            [['callerId', 'callId'], 'string', 'max' => 40],
        ];
        return $this->getExtraField($rules);
    }

    public function getIdQueue()
    {
        return $this->hasOne(Queue::class, ['id' => 'id_queue']);
    }
}
