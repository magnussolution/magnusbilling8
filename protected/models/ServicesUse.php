<?php

/**
 * Modelo para a tabela "DidUse".
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
 * 24/09/2017
 */

namespace app\models;

use Yii;
use app\components\Model;

class  ServicesUse extends Model
{
    protected $_module = 'servicesuse';
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
        return 'pkg_services_use';
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
            [['id_user', 'id_services', 'status', 'month_payed', 'reminded'], 'integer', 'integerOnly' => true],
            [['reservationdate', 'releasedate', 'contract_period', 'termination_date', 'next_due_date'], 'safe'],
        ];
        return $this->getExtraField($rules);
    }

    /**
     * @return array regras de relacionamento.
     */
    public function getIdServices()
    {
        return $this->hasOne(Services::class, ['id' => 'id_services']);
    }

    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }

    public function beforeSave($insert)
    {
        if ($this->getIsNewRecord()) {
            $this->status = 2;
        }

        return parent::beforeSave($insert);
    }
}
