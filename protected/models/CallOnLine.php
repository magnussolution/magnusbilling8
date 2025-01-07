<?php

/**
 * Modelo para a tabela "CallOnLine".
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
use \Exception;

class  CallOnLine extends Model
{
    protected $_module = 'callonline';
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
        return 'pkg_call_online';
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
            [['id_user'], 'integer', 'integerOnly' => true],
            [['canal, tronco, from_ip', 'sip_account'], 'string', 'max' => 50],
            [['ndiscado, status', 'duration'], 'string', 'max' => 16],
            [['codec', 'reinvite'], 'string', 'max' => 5],
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

    public function insertCalls($sql)
    {

        $sql = 'INSERT INTO pkg_call_online VALUES ' . implode(',', $sql) . ';';
        try {
            return Yii::$app->db->createCommand($sql)->execute();
        } catch (Exception $e) {
            return $e;
        }
    }
}
