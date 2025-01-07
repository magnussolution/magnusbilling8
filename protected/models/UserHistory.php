<?php

/**
 * Modelo para a tabela "pkg_ui_authen".
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

class  UserHistory extends Model
{
    protected $_module = 'userhistory';
    public $sumCredit;
    public $sumCreditMonth;
    public $CreditMonth;
    /**
     * Retorna a classe estatica da model.
     * @return Admin classe estatica da model.
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
        return 'pkg_user_history';
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
            [['date'], 'string', 'max' => 20],
            [['description'], 'string', 'max' => 1000],

        ];
        return $this->getExtraField($rules);
    }
    /*
     * @return array regras de relacionamento.
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }
}
