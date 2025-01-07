<?php

/**
 * Modelo para a tabela "Voucher".
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
 * 20/09/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class  Voucher extends Model
{
    protected $_module = 'voucher';
    /**
     * Retorna a classe estatica da model.
     * @return Payment classe estatica da model.
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
        return 'pkg_voucher';
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
            [['voucher'], 'required'],
            [['used', 'id_user', 'id_plan'], 'integer', 'integerOnly' => true],
            [['credit'], 'integer'],
            [['voucher', 'tag', 'language', 'prefix_local'], 'string', 'max' => 50],
            [['usedate', 'expirationdate'], 'safe'],
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

    public function beforeSave($insert)
    {

        if ($this->used == 0) {
            $this->usedate = '0000-00-00 00:00:00';
        } else if ($this->used == 1 && $this->usedate == '0000-00-00 00:00:00') {
            $success = false;
            $msg     = 'It is not possible to use this option';
            # retorna o resultado da execucao
            echo json_encode([
                'success' => $success,
                'msg'     => $msg,
            ]);
            exit;
        }
        if ($this->getIsNewRecord()) {
            $this->id_user = null;
        }
        return parent::beforeSave($insert);
    }

    public function afeterSave()
    {
        return parent::afeterSave();
    }
}
