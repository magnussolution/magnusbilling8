<?php

/**
 * Modelo para a tabela "Trunk".
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
 * 25/06/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

namespace app\models;

use Yii;
use app\components\Model;

class  TrunkSipCodes extends Model
{
    protected $_module = 'trunk';
    public $percentage;

    /**
     * Retorna a classe estatica da model.
     * @return Trunk classe estatica da model.
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
        return 'pkg_trunk_error';
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
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        $rules = [
            [['ip', 'code', 'total'], 'required'],
            [['total', 'code', 'integer'], 'integerOnly' => true],
            [['ip'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 5],
            [['total'], 'string', 'max' => 11],

        ];
        return $this->getExtraField($rules);
    }
}
