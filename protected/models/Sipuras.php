<?php

/**
 * Modelo para a tabela "Sipuras".
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
use app\components\LoadConfig;

class  Sipuras extends Model
{
    protected $_module = 'sipuras';
    public $remote;
    /**
     * Retorna a classe estatica da model.
     * @return Sipuras classe estatica da model.
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
        return 'pkg_sipura';
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
            [['macadr', 'id_user'], 'required'],
            [['macadr'], 'string', 'max' => 12],
            [[
                'senha_admin',
                'senha_user',
                'Preferred_Codec_1',
                'Preferred_Codec_2'
            ], 'string', 'max' => 8],
            [[
                'antireset',
                'Enable_Web_Server',
                'STUN_Enable',
                'NAT_Keep_Alive_Enable_1_',
                'NAT_Keep_Alive_Enable_2_',
                'NAT_Mapping_Enable_1_',
                'NAT_Mapping_Enable_2_',
                'STUN_Test_Enable',
                'Substitute_VIA_Addr',
                'Use_Pref_Codec_Only_1',
                'Use_Pref_Codec_Only_2',
            ], 'integer'],
            [['lastmov'], 'string', 'max' => 20],
            [[
                'marca',
                'altera',
            ], 'string', 'max' => 2],
            [['obs'], 'string', 'max' => 50],
            [['Proxy_1', 'Proxy_2'], 'string', 'max' => 100],
            [['last_ip', 'nserie'], 'string', 'max' => 15],
            [['Register_Expires_1', 'Register_Expires_2'], 'string', 'max' => 4],
            [['fultmov'], 'string', 'max' => 30],
            [['User_ID_1', 'User_ID_2', 'Password_1', 'Password_2'], 'string', 'max' => 25],
            [['STUN_Server', 'Dial_Tone'], 'string', 'max' => 80],
            [['Dial_Plan_1', 'Dial_Plan_2'], 'string', 'max' => 180],
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

    public function beforeSave($insert)
    {
        $config       = LoadConfig::getConfig();
        $this->altera = $this->remote == 1 ? $this->altera : 'si';
        if ($this->getIsNewRecord()) {
            $this->fultmov = '0000-00-00';
        }
        return parent::beforeSave($insert);
    }
}
