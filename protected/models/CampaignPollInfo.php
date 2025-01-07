<?php

/**
 * Modelo para a tabela "CampaignPollInfo".
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

class  CampaignPollInfo extends Model
{
    protected $_module = 'campaignpollinfo';
    public $sumresposta;
    public $resposta2;
    public $resposta_name;
    public $percentage;
    public $total_votos;
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
        return 'pkg_campaign_poll_info';
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
            [['id_campaign_poll'], 'integer', 'integerOnly' => true],
            [['number'], 'string', 'max' => 18],
            [['obs', 'resposta'], 'string', 'max' => 200],
        ];
        return $this->getExtraField($rules);
    }

    /**
     * @return array regras de relacionamento.
     */
    public function getIdCampaignPoll()
    {
        return $this->hasOne(CampaignPoll::class, ['id' => 'id_campaign_poll']);
    }
}
