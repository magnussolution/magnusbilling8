<?php

/**
 * Modelo para a tabela "Methodpay".
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

class  Methodpay extends Model
{
    protected $_module = 'methodpay';
    /**
     * Retorna a classe estatica da model.
     *
     * @return GroupUserActionSubModule classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     *
     *
     * @return nome da tabela.
     */
    public static function tableName()
    {
        return 'pkg_method_pay';
    }

    /**
     *
     *
     * @return nome da(s) chave(s) primaria(s).
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     *
     *
     * @return array validacao dos campos da model.
     */
    public function rules()
    {
        $rules = [
            [['id', 'payment_method', 'country'], 'required'],
            [['active', 'id_user', 'fee', 'SLIdProduto', 'min', 'max'], 'integer', 'integerOnly' => true],
            [['obs', 'client_id', 'client_secret'], 'string', 'max' => 500],
            [['P2P_tax_amount'], 'string', 'max' => 10],
            [['P2P_CustomerSiteID', 'P2P_KeyID', 'P2P_Passphrase', 'P2P_RecipientKeyID'], 'string', 'max' => 100],
            [['pagseguro_TOKEN', 'url', 'show_name', 'SLvalidationtoken'], 'string', 'max' => 100],
            [['SLAppToken', 'SLAccessToken', 'SLSecret'], 'string', 'max' => 50],
            [['username'], 'string', 'max' => 1000],
        ];
        return $this->getExtraField($rules);
    }

    /**
     *
     *
     * @return array regras de relacionamento.
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }
}
