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

class  Queue extends Model
{
    protected $_module = 'queue';
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
        return 'pkg_queue';
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
            [['name', 'id_user'], 'required'],
            [['id_user', 'timeout', 'retry', 'wrapuptime', 'weight', 'periodic-announce-frequency', 'max_wait_time'], 'integer', 'integerOnly' => true],
            [['language', 'joinempty', 'leavewhenempty', 'musiconhold', 'announce-holdtime', 'leavewhenempty', 'strategy', 'ringinuse', 'announce-position', 'announce-holdtime', 'announce-frequency'], 'string', 'max' => 128],
            [['periodic-announce'], 'string', 'max' => 200],
            [['ring_or_moh'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 25],
            [['max_wait_time_action'], 'string', 'max' => 50],
            [['name'], 'checkname'],
            [['max_wait_time_action'], 'check_max_wait_time_action'],

        ];
        return $this->getExtraField($rules);
    }

    /**
     * @return array regras de relacionamento.
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }

    public function check_max_wait_time_action($attribute, $params)
    {
        if (strlen($this->max_wait_time_action) > 2) {
            if (preg_match('/\//', $this->max_wait_time_action)) {

                if (preg_match('/LOCAL/', strtoupper($this->max_wait_time_action))) {
                    return;
                } else {
                    $data        = explode('/', $this->max_wait_time_action);
                    $type        = strtoupper($data[0]);
                    $destination = $data[1];

                    switch ($type) {
                        case 'SIP':
                            $model = Sip::find('UPPER(name) = :key', [':key' => strtoupper($destination)])->one();
                            break;
                        case 'QUEUE':
                            $model = Queue::find('UPPER(name)  = :key', [':key' => strtoupper($destination)])->one();
                            break;
                        case 'IVR':
                            $model = Ivr::find('UPPER(name)  = :key', [':key' => strtoupper($destination)])->one();
                            break;
                    }
                }
                if (! isset($model->id)) {
                    $this->addError($attribute, Yii::t('zii', 'You need add a existent Sip Account, IVR or Queue.'));
                }
                $this->max_wait_time_action = $type . '/' . $destination;
            }
        }
    }

    public function checkname($attribute, $params)
    {
        if (preg_match('/ /', $this->name)) {
            $this->addError($attribute, Yii::t('zii', 'No space allow in name'));
        }

        if (! preg_match('/^[0-9]|^[A-Z]|^[a-z]/', $this->name)) {
            $this->addError($attribute, Yii::t('zii', 'Name need start with numbers or letters'));
        }
    }

    public function beforeSave($insert)
    {
        if (! $this->getIsNewRecord()) {
            $model = Queue::findOne($this->id);

            QueueMember::updateAll(['queue_name' => $this->name], ['queue_name' => $model->name]);
        }
        return parent::beforeSave($insert);
    }

    public static function truncateQueueStatus()
    {
        $sql = "TRUNCATE pkg_queue_status";
        Yii::$app->db->createCommand($sql)->execute();
    }

    public static function deleteQueueStatus($id)
    {
        $sql = "DELETE FROM pkg_queue_status WHERE callId = " . $id;
        Yii::$app->db->createCommand($sql)->execute();
    }

    public static function updateQueueStatus($operator, $holdtime, $uniqueid)
    {
        $sql = "UPDATE pkg_queue_status SET status = 'answered', agentName = :key,
                    holdtime = :key2  WHERE callId = :key3 ";
        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(":key", $operator, \PDO::PARAM_STR);
        $command->bindValue(":key2", $holdtime, \PDO::PARAM_STR);
        $command->bindValue(":key3", $uniqueid, \PDO::PARAM_STR);
        $command->execute();
    }
    public static function getQueueStatus($agentName, $id_queue)
    {
        $sql     = "SELECT * FROM pkg_queue_status WHERE agentName = :key AND id_queue = :key1";
        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(":key", $agentName, \PDO::PARAM_STR);
        $command->bindValue(":key1", $id_queue, \PDO::PARAM_STR);
        return $command->queryAll();
    }

    public static function getQueueAgentStatus($id)
    {
        $sql     = "SELECT agentName FROM pkg_queue_agent_status WHERE id = :key";
        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(":key", $id, \PDO::PARAM_STR);
        return $command->queryAll();
    }

    public static function insertQueueStatus($id_queue, $uniqueid, $queueName, $callerId, $channel)
    {
        $sql = "INSERT INTO pkg_queue_status (id_queue, callId, queue_name, callerId, time, channel, status)
                        VALUES (" . $id_queue . ", '" . $uniqueid . "', '$queueName', '" . $callerId . "',
                        '" . date('Y-m-d H:i:s') . "', '" . $channel . "', 'ringing')";
        Yii::$app->db->createCommand($sql)->execute();
    }
}
