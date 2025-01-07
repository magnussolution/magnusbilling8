<?php

/**
 * Modelo para a tabela "Campaign".
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
 * 28/10/2012
 */

namespace app\models;

use Yii;
use app\components\Model;

class  CampaignLog extends Model
{
    protected $_module = 'campaignlog';
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
        return 'pkg_campaign_log';
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
            [['total'], 'required'],
            [['loops'], 'integer', 'integerOnly' => true],
            [['trunks', 'campaigns'], 'string', 'max' => 100],
            [['date'], 'string', 'max' => 50],

        ];
        return $this->getExtraField($rules);
    }
}
