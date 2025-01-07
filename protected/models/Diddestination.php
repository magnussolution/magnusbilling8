<?php

/**
 * Modelo para a tabela "Diddestination".
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
 * 24/09/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class  Diddestination extends Model
{
    protected $_module = 'diddestination';

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
        return 'pkg_did_destination';
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
            [['id_user'], 'required'],
            [['id_user', 'id_queue', 'id_sip', 'id_ivr', 'id_did', 'priority', 'activated', 'secondusedreal', 'voip_call'], 'integer', 'integerOnly' => true],
            [['destination'], 'string', 'max' => 120],
            [['context'], 'safe'],
        ];
        return $this->getExtraField($rules);
    }

    /**
     * @return array regras de relacionamento.
     */
    public function getIdDid()
    {
        return $this->hasOne(Did::class, ['id' => 'id_did']);
    }

    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }

    public function getIdIvr()
    {
        return $this->hasOne(Ivr::class, ['id' => 'id_ivr']);
    }

    public function getIdQueue()
    {
        return $this->hasOne(Queue::class, ['id' => 'id_queue']);
    }

    public function getIdSip()
    {
        return $this->hasOne(Sip::class, ['id' => 'id_sip']);
    }

    public function beforeSave($insert)
    {
        if ($this->getIsNewRecord()) {
            $this->creationdate = date('Y-m-d H:i:s');
        }

        return parent::beforeSave($insert);
    }
}
