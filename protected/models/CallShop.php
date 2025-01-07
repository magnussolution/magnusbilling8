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

class  CallShop extends Model
{
    protected $_module = 'callshop';
    public $priceSum;
    public $callshopdestination;
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
        return 'pkg_sip';
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
            [['id_user', 'calllimit'], 'integer', 'integerOnly' => true],
            [['name', 'callerid', 'context', 'fromuser', 'fromdomain', 'md5secret', 'secret', 'fullcontact'], 'string', 'max' => 80],
            [['regexten', 'insecure', 'regserver', 'vmexten', 'callingpres', 'mohsuggest', 'allowtransfer', 'callshoptime'], 'string', 'max' => 20],
            [['amaflags', 'dtmfmode', 'qualify'], 'string', 'max' => 7],
            [['callgroup', 'pickupgroup', 'auth', 'subscribemwi', 'usereqphone', 'autoframing'], 'string', 'max' => 10],
            [['DEFAULTip', 'accountcode', 'ipaddr', 'maxcallbitrate', 'rtpkeepalive'], 'string', 'max' => 15],
            [['host'], 'string', 'max' => 31],
            [['language'], 'string', 'max' => 2],
            [['mailbox'], 'string', 'max' => 50],
            [['nat', 'rtptimeout', 'rtpholdtimeout'], 'string', 'max' => 3],
            [['deny', 'permit'], 'string', 'max' => 95],
            [['port'], 'string', 'max' => 5],
            [['type'], 'string', 'max' => 6],
            [['disallow, allow, setvar', 'useragent'], 'string', 'max' => 100],
            [['lastms'], 'string', 'max' => 11],
            [['defaultuser', 'cid_number', 'outboundproxy'], 'string', 'max' => 40],
        ];

        $rules = $this->getExtraField($rules);

        return $rules;
    }
    /**
     * @return array relational rules.
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }
}
