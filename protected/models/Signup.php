<?php

/**
 * Modelo para a tabela "Signup".
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
use yii\captcha\Captcha;
use app\components\Model;
use app\components\LoadConfig;
use app\components\ValidaCPFCNPJ;

class  Signup extends Model
{
    protected $_module = 'signup';
    public $verifyCode;
    public $password2;
    public $accept_terms;
    public $captcha = true;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return nome da tabela.
     */
    public static function tableName()
    {
        return 'pkg_user';
    }

    /**
     * @return array validacao dos campos da model.
     */
    public function rules()
    {

        $rules = [
            [['username', 'password', 'lastname', 'firstname', 'email', 'city', 'state', 'phone', 'id_plan', 'id_user'], 'required'],
            [['phone', 'vat', 'mobile,calllimit'], 'integer'],
            [['password', 'password2'], 'string', 'min' => 6],
            [['lastname', 'firstname', 'city', 'state'], 'string', 'min' => 2],
            [['country'], 'string', 'min' => 1],
            [['zipcode'], 'string', 'min' => 5],
            [['doc'], 'string', 'min' => 11],
            [['username'], 'string', 'min' => 5],
            [['username'], 'checkusername'],
            [['password'], 'checksecret'],
            [['doc'], 'checkdoc'],
            [['state_number'], 'string', 'max' => 40],
            [['neighborhood'], 'string', 'max' => 50],
            [['address', 'company_name'], 'string', 'max' => 100],
            [['mobile', 'phone'], 'string', 'min' => 10],
            [['email'], 'checkemail'],
            [['email', 'username'], 'unique'],
            [['verifyCode'], 'captcha', 'captchaAction' => 'site/captcha', 'skipOnEmpty' => !Captcha::checkRequirements() || $this->captcha == false],
            [['accept_terms'], 'required', 'requiredValue' => 1, 'message' => 'You must accept the Terms and Conditons in order to register.'],
        ];
        return $this->getExtraField($rules);
    }

    public function checkemail($attribute, $params)
    {
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError($attribute, Yii::t('zii', 'Invalid Email'));
        }
    }

    public function checkdoc($attribute, $params)
    {
        $signup = new Signup();
        $config = LoadConfig::getConfig();
        if ($config['global']['base_language'] == 'pt_BR') {

            $cpf_cnpj = new ValidaCPFCNPJ($this->doc);
            // Opção de CPF ou CNPJ formatado no padrão
            $formatado = $cpf_cnpj->formata();

            // Verifica se o CPF ou CNPJ é válido
            if ($formatado) {
                $this->doc = $formatado;
            } else {
                $this->addError($attribute, Yii::t('zii', 'CPF ou CNPJ Inválido'));
            }
        }

        if ($config['global']['signup_unique_doc'] == 0 && strlen($this->doc)) {
            $modelUserCheck = User::find()->where(['doc' => $this->doc])->one();
            if (isset($modelUserCheck->id)) {
                $this->addError($attribute, Yii::t('zii', 'This DOC is already used per other user'));
            }
        }
    }
    public function checkusername($attribute, $params)
    {
        if (preg_match('/ /', $this->username)) {
            $this->addError($attribute, Yii::t('zii', 'No space allow in username'));
        }

        if (! preg_match('/^[1-9]|^[A-Z]|^[a-z]/', $this->username)) {
            $this->addError($attribute, Yii::t('zii', 'Username need start with numbers or letters'));
        }
    }
    public function checksecret($attribute, $params)
    {
        if (preg_match('/ /', $this->password)) {
            $this->addError($attribute, Yii::t('zii', 'No space allow in password'));
        }

        if ($this->password == '123456' || $this->password == '12345678' || $this->password == '012345') {
            $this->addError($attribute, Yii::t('zii', 'No use sequence in the password'));
        }

        if ($this->password == $this->username) {
            $this->addError($attribute, Yii::t('zii', 'Password cannot be equal username'));
        }
    }

    public function beforeSave($insert)
    {
        $this->company_name = strtoupper($this->company_name);
        $this->state_number = strtoupper($this->state_number);
        $this->city         = strtoupper($this->city);
        $this->address      = strtoupper($this->address);
        return parent::beforeSave($insert);
    }
}
