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
 * 17/08/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class  CallFailed extends Model
{
    protected $_module = 'callfailed';
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
        return 'pkg_cdr_failed';
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
            [['id_user', 'calledstation'], 'required'],
            [['id_user', 'id_plan', 'id_trunk', 'id_server', 'sipiax', 'terminatecauseid,hangupcause'], 'integer', 'integerOnly' => true],
            [['uniqueid', 'starttime', 'callerid', 'src', 'calledstation'], 'string', 'max' => 50],
        ];
        return $this->getExtraField($rules);
    }

    public function getIdPrefix()
    {
        return $this->hasOne(Prefix::class, ['id' => 'id_prefix']);
    }

    public function getIdPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'id_plan']);
    }

    public function getIdTrunk()
    {
        return $this->hasOne(Trunk::class, ['id' => 'id_trunk']);
    }

    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }

    public function getIdServer()
    {
        return $this->hasOne(Servers::class, ['id' => 'id_server']);
    }

    public static function createDataBaseIfNotExist()
    {
        $sql = "CREATE TABLE IF NOT EXISTS pkg_cdr_failed_archive (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `id_user` int(11) NOT NULL,
              `id_plan` int(11) DEFAULT NULL,
              `id_trunk` int(11) DEFAULT NULL,
              `id_prefix` int(11) DEFAULT NULL,
              `uniqueid` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `calledstation` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `sipiax` int(11) DEFAULT '0',
              `src` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `terminatecauseid` int(1) DEFAULT '1',
              PRIMARY KEY (`id`),
              KEY `id_user` (`id_user`),
              KEY `id_plan` (`id_plan`),
              KEY `id_trunk` (`id_trunk`),
              KEY `calledstation` (`calledstation`),
              KEY `terminatecauseid` (`terminatecauseid`),
              KEY `id_prefix` (`id_prefix`),
              KEY `uniqueid` (`uniqueid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        Yii::$app->db->createCommand($sql)->execute();
    }
}
