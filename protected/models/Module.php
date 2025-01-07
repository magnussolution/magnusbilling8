<?php

/**
 * Modelo para a tabela "Module".
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



class  Module extends Model
{
    protected $_module = 'module';
    /**
     * Return the static class of model.
     * @return Module classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return name of table.
     */
    public static function tableName()
    {
        return 'pkg_module';
    }

    /**
     * @return name of primary key(s).
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @return array validation of fields of model.
     */
    public function rules()
    {
        $rules = [
            [['text'], 'required'],
            [['id_module', 'priority'], 'integer', 'integerOnly' => true],
            [['text', 'icon_cls'], 'string', 'max' => 100],
        ];
        return $this->getExtraField($rules);
    }

    /**
     * @return array roles of relationship.
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::class, ['id_module' => 'id'])->viaTable('group_module', ['id_module' => 'id_module']);
    }

    public function getGroupModules()
    {
        return $this->hasMany(GroupModule::class, ['id_module' => 'id']);
    }

    public function getIdModule()
    {
        return $this->hasOne(Module::class, ['id' => 'id_module']);
    }

    public function getModules()
    {
        return $this->hasMany(Module::class, ['id_module' => 'id']);
    }

    public function getIdUserType()
    {
        return $this->hasOne(UserType::class, ['id' => 'id_user_type']);
    }
}
