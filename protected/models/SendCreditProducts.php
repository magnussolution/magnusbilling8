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

class  SendCreditProducts extends Model
{
    protected $_module = 'sendcreditproducts';
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
        return 'pkg_send_credit_products';
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
            [['country_code', 'status'], 'integer', 'integerOnly' => true],
            [['country', 'operator_name', 'info'], 'string', 'max' => 100],
            [['product', 'send_value', 'wholesale_price', 'provider'], 'string', 'max' => 50],
            [['currency_dest', 'currency_orig'], 'string', 'max' => 3],
            [['SkuCode'], 'string', 'max' => 30],
            [['operator_id'], 'string', 'max' => 11],
            [['type', 'retail_price'], 'string', 'max' => 50],

        ];
        return $this->getExtraField($rules);
    }
}
