<?php

/**
 * Modelo para a tabela "Trunk".
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
 * 25/06/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class Trunk extends Model
{
    protected $_module = 'trunk';
    /**
     * Retorna a classe estatica da model.
     * @return Trunk classe estatica da model.
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
        return 'pkg_trunk';
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
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        $rules = [
            [['trunkcode', 'id_provider', 'allow', 'providertech', 'host'], 'required'],
            [['allow_error', 'id_provider', 'failover_trunk', 'secondusedreal', 'register', 'call_answered', 'port', 'call_total', 'inuse', 'maxuse', 'status', 'if_max_use', 'cnl'], 'integer', 'integerOnly' => true],
            [['secret'], 'string', 'max' => 50],
            [['nat', 'trunkcode', 'sms_res'], 'string', 'max' => 50],
            [['trunkprefix', 'providertech', 'removeprefix', 'context', 'insecure', 'disallow'], 'string', 'max' => 20],
            [['providerip', 'user', 'fromuser', 'allow', 'host', 'fromdomain'], 'string', 'max' => 80],
            [['addparameter', 'block_cid'], 'string', 'max' => 120],
            [['link_sms'], 'string', 'max' => 250],
            [['dtmfmode', 'qualify'], 'string', 'max' => 7],
            [['directmedia', 'sendrpid'], 'string', 'max' => 10],
            [['cid_add', 'cid_remove'], 'string', 'max' => 11],
            [['type', 'language'], 'string', 'max' => 6],
            [['transport', 'encryption'], 'string', 'max' => 3],
            [['register_string'], 'string', 'max' => 300],
            [['sip_config'], 'string', 'max' => 500],
            [['trunkcode'], 'checkTrunkCode'],
            [['trunkcode'], 'uniquePeerName'],
            [['trunkcode'], 'unique'],
        ];
        return $this->getExtraField($rules);
    }

    public function checkTrunkCode($attribute, $params)
    {
        if ($this->host == 'dynamic' && $this->trunkcode != $this->user) {
            $this->addError($attribute, Yii::t('zii', 'When host =dynamic the trunk name and username need be equal.'));
        }
    }

    /**
     * @return array regras de relacionamento.
     */
    public function getIdProvider()
    {
        return $this->hasOne(Provider::class, ['id' => 'id_provider']);
    }

    public function getTrunks()
    {
        return $this->hasMany(Trunk::class, ['failover_trunk' => 'id']);
    }

    public function beforeSave($insert)
    {
        $this->register_string = $this->register == 1 ? $this->register_string : '';
        $this->providerip      = $this->providertech != 'sip' && $this->providertech != 'iax2' ? $this->host : $this->trunkcode;
        return parent::beforeSave($insert);
    }
}
