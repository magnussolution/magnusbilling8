<?php

/**
 * Modelo para a tabela "GroupUser".
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



class  GroupUser extends Model
{
    protected $_module = 'groupuser';

    /**
     * Return the static class of model.
     *
     * @return GroupUser classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     *
     *
     * @return name of table.
     */
    public static function tableName()
    {
        return 'pkg_group_user';
    }

    /**
     *
     *
     * @return name of primary key(s).
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     *
     *
     * @return array validation of fields of model.
     */
    public function rules()
    {
        $rules = [
            [['name'], 'required'],
            [['id_user_type', 'hidden_prices', 'hidden_batch_update'], 'integer', 'integerOnly' => true],
            [['name'], 'string', 'max' => 100],
            [['user_prefix'], 'string', 'max' => 6],
        ];
        return $this->getExtraField($rules);
    }

    /**
     *
     *
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

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id_group' => 'id']);
    }

    public function getIdUserType()
    {
        return $this->hasOne(UserType::class, ['id' => 'id_user_type']);
    }
}
