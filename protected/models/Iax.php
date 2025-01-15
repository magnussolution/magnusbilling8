<?php

/**
 * Modelo para a tabela "Iax".
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
 * 19/06/2016
 */

namespace app\models;

use Yii;
use app\components\Model;

class  Iax extends Model
{
    protected $_module = 'iax';
    /**
     * Retorna a classe estatica da model.
     *
     * @return Iax classe estatica da model.
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
        return 'pkg_iax';
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
            [['id_user'], 'required'],
            [['id_user', 'calllimit '], 'integer', 'integerOnly' => true],
            [['name', 'callerid', 'context', 'fromuser', 'fromdomain', 'md5secret', 'secret'], 'string', 'max' => 80],
            [['regexten', 'insecure', 'accountcode'], 'string', 'max' => 20],
            [['amaflags', 'dtmfmode', 'qualify'], 'string', 'max' => 7],
            [['callgroup', 'pickupgroup'], 'string', 'max' => 10],
            [['DEFAULTip', 'ipaddr'], 'string', 'max' => 15],
            [['nat', 'host'], 'string', 'max' => 31],
            [['language'], 'string', 'max' => 2],
            [['mailbox'], 'string', 'max' => 50],
            [['rtpholdtimeout'], 'string', 'max' => 3],
            [['deny', 'permit'], 'string', 'max' => 95],
            [['port'], 'string', 'max' => 5],
            [['type'], 'string', 'max' => 6],
            [['disallow', 'allow', 'useragent'], 'string', 'max' => 100],
            [['username'], 'checkusername'],
            [['username'], 'unique'],
        ];
        return $this->getExtraField($rules);
    }

    public function checkusername($attribute, $params)
    {
        if (preg_match('/ /', $this->username)) {
            $this->addError($attribute, Yii::t('zii', 'No space allow in username'));
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        $sql = "UPDATE pkg_iax SET accountcode = ( SELECT username FROM pkg_user WHERE pkg_user.id = pkg_iax.id_user)";
        Yii::$app->db->createCommand($sql)->execute();

        return parent::afterSave($insert, $changedAttributes);
    }

    /*
     * @return array regras de relacionamento.
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }
}
