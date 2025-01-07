<?php

/**
 * Modelo para a tabela "Plan".
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
 * 24/07/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class  Portability extends Model
{
    protected $_module = 'portability';
    /**
     * Retorna a classe estatica da model.
     * @return SubModule classe estatica da model.
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
        return 'pkg_portabilidade';
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
            [['number'], 'integer', 'integer' => true],
            [['number'], 'string', 'max' => 15],
            [['company'], 'string', 'max' => 5],
            [['date'], 'string', 'max' => 30],
        ];
        return $this->getExtraField($rules);
    }

    public function findPrefix($prefix)
    {
        $sql     = "SELECT company FROM pkg_portabilidade_prefix  WHERE number = :key ORDER BY number DESC LIMIT 1";
        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(":key", $prefix, \PDO::PARAM_INT);
        return $command->queryAll();
    }
}
