<?php


/**
 * Modelo para a tabela "User".
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



class  User extends Model
{
    public $newPassword = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pkg_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['username', 'password'], 'required'],
            [[
                'id_user',
                'id_group',
                'id_plan',
                'id_offer',
                'active',
                'enableexpire',
                'expiredays',
                'typepaid',
                'creditlimit',
                'credit_notification',
                'sipaccountlimit',
                'restriction',
                'callingcard_pin',
                'callshop',
                'plan_day',
                'active_paypal',
                'boleto',
                'boleto_day',
                'calllimit',
                'disk_space',
                'id_group_agent',
                'transfer_dbbl_rocket_profit',
                'transfer_bkash_profit',
                'transfer_flexiload_profit',
                'transfer_international_profit',
                'transfer_dbbl_rocket',
                'transfer_bkash',
                'transfer_flexiload',
                'transfer_international',
                'transfer_bdservice_rate',
                'transfer_show_selling_price',
                'cpslimit',
                'restriction_use',
                'credit_notification_daily',
                'email_services',
                'email_did',
                'inbound_call_limit'
            ], 'integer'],
            [['language', 'mix_monitor_format', 'calllimit_error'], 'string', 'max' => 5],
            [['zipcode', 'phone', 'mobile', 'vat'], 'string', 'max' => 20],
            [['city', 'state', 'country', 'loginkey'], 'string', 'max' => 40],
            [['lastname', 'firstname', 'redial', 'neighborhood'], 'string', 'max' => 50],
            [['company_website', 'dist'], 'string', 'max' => 100],
            [['address', 'email', 'email2', 'doc'], 'string', 'max' => 100],
            [['username'], 'string', 'min' => 4, 'max' => 20],
            [['description', 'prefix_local'], 'string', 'max' => 500],
            [['credit', 'contract_value'], 'number'],
            [['expirationdate', 'password', 'lastuse', 'company_name', 'commercial_name'], 'string', 'max' => 100],
            [['username'], 'checkUsername'],
            [['password'], 'checkSecret'],
            [['username'], 'unique', 'targetClass' => self::class, 'message' => 'Username already exists'],
        ];

        return $this->getExtraField($rules);
    }

    /**
     * Validação personalizada do nome de usuário.
     */
    public function checkUsername($attribute, $params)
    {
        if (preg_match('/ /', $this->username)) {
            $this->addError($attribute, Yii::t('app', 'No space allowed in username'));
        }

        if (!preg_match('/^[0-9A-Za-z]/', $this->username)) {
            $this->addError($attribute, Yii::t('app', 'Username must start with numbers or letters'));
        }
    }

    /**
     * Validação personalizada da senha.
     */
    public function checkSecret($attribute, $params)
    {
        if (preg_match('/ /', $this->password)) {
            $this->addError($attribute, Yii::t('app', 'No space allowed in password'));
        }

        if (in_array($this->password, [['123456'], '12345678', '012345'])) {
            $this->addError($attribute, Yii::t('app', 'Do not use sequences in the password'));
        }

        if ($this->password === $this->username) {
            $this->addError($attribute, Yii::t('app', 'Password cannot be equal to username'));
        }
    }

    /**
     * Antes de salvar, ajusta alguns campos, como a data de criação.
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->creationdate = date('Y-m-d H:i:s');
        }

        $this->contract_value = $this->contract_value == '' ? 0 : $this->contract_value;

        return parent::beforeSave($insert);
    }

    /**
     * Relacionamentos com outras tabelas.
     */


    public function getIdGroup()
    {
        return $this->hasOne(GroupUser::class, ['id' => 'id_group']);
    }

    public function getIdPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'id_plan']);
    }

    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }
}
