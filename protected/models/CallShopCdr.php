<?php

/**
 * Modelo para a tabela "Alarm".
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
 * 17/08/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class  CallShopCdr extends Model
{
    protected $_module = 'callshopcdr';

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
        return 'pkg_callshop';
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
            [['id_user', 'status', 'sessiontime'], 'integer', 'integerOnly' => true],
            [['price', 'buycost', 'markup,price_min'], 'string', 'max' => 20],
            [['cabina'], 'string', 'max' => 30],
            [['calledstation', 'sessionid'], 'string', 'max' => 50],
            [['destination', 'sessionid'], 'string', 'max' => 100],
        ];
        return $this->getExtraField($rules);
    }
}
