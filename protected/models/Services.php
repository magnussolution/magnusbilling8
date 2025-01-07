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

class  Services extends Model
{
    protected $_module = 'services';
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
        return 'pkg_services';
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
            [['name', 'price'], 'required'],
            [['status', 'disk_space', 'sipaccountlimit', 'calllimit', 'return_credit'], 'integer', 'integerOnly' => true],
            [['description'], 'string', 'min' => 1],
            [['type'], 'string', 'max' => 50],
        ];
        return $this->getExtraField($rules);
    }
    /**
     * @return array roles of relationship.
     */
    public function getModules()
    {
        return $this->hasMany(Module::class, ['id' => 'id_module'])
            ->viaTable('group_module', ['id_group' => 'id']);
    }

    public function getGroupModules()
    {
        return $this->hasMany(GroupModule::class, ['id_group' => 'id']);
    }
}
