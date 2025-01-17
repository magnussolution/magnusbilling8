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



class  LogUsers extends Model
{
    protected $_module = 'logusers';
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
        return 'pkg_log';
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
        return [
            [['id_user', 'id_log_actions'], 'integer'],
            [['date'], 'string', 'max' => 100],
            [['ip'], 'string', 'max' => 16],
            [['description'], 'safe'],
        ];
    }

    /**
     * @return array regras de relacionamento.
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'id']);
    }

    public function getIdLogActions()
    {
        return $this->hasOne(LogActions::class, ['id_log_actions' => 'id']);
    }
}
