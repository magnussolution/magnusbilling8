<?php

/**
 * Modelo para a tabela "CallBack".
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

use app\components\Model;
use app\components\LoadConfig;
use app\components\Util;
use app\models\User;
use app\models\Did;

class  CallBack extends Model
{
    protected $_module = 'callback';
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
        return 'pkg_callback';
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
            [['id_user,num_attempt', 'id_server_group', 'id_did', 'sessiontime'], 'integer', 'integerOnly' => true],
            [['uniqueid', 'server_ip'], 'string', 'max' => 40],
            [['status', 'callerid'], 'string', 'max' => 10],
            [['channel', 'exten', 'account', 'context', 'timeout', 'priority'], 'string', 'max' => 60],
            [['variable'], 'string', 'max' => 300],
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

    public function getIdDid()
    {
        return $this->hasOne(Did::class, ['id' => 'id_did']);
    }

    public function beforeSave($insert)
    {
        if ($this->getIsNewRecord()) {
            $config = LoadConfig::getConfig();

            $modelUser   = User::model()->findByPk((int) $this->id_user);
            $this->exten = Util::number_translation($modelUser->prefix_local, $this->exten);
        }
        return parent::beforeSave($insert);
    }

    public function verbose()
    {
        return;
    }
}
