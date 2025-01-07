<?php

/**
 * Modelo para a tabela "Plan".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 */

namespace app\models;

use Yii;
use app\components\Model;



class  Plan extends Model
{
    protected $_module = 'plan';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pkg_plan';
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * Define as regras de validação dos campos da model.
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['id_user', 'tariff_limit', 'play_audio', 'techprefix', 'lcrtype', 'signup', 'portabilidadeMobile', 'portabilidadeFixed'], 'integer'],
            [['name', 'ini_credit'], 'string', 'max' => 50],
            [['techprefix'], 'string', 'max' => 5],
            [['name'], 'unique', 'targetAttribute' => ['name'], 'message' => 'This name is already taken.'],
            [['creationdate'], 'safe'],
        ];
    }

    /**
     * Define os relacionamentos da model.
     * @return array
     */
    public function getIdUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }

    /**
     * Realiza a busca de tarifa com base no id do plano e destino.
     * @param int $id_plan
     * @param string $destination
     * @return array
     */
    public function searchTariff($id_plan, $destination)
    {
        $max_len_prefix = strlen($destination);
        $prefixclause = '(';

        while ($max_len_prefix >= 1) {
            $prefixclause .= "prefix='" . substr($destination, 0, $max_len_prefix) . "' OR ";
            $max_len_prefix--;
        }

        $prefixclause = substr($prefixclause, 0, -3) . ")";

        $sql = "SELECT pkg_plan.id AS id_plan, pkg_prefix.prefix AS dialprefix, pkg_plan.name, pkg_rate.id_prefix, 
                       pkg_rate.id AS id_rate, minimal_time_charge, rateinitial, initblock, billingblock, 
                       connectcharge, disconnectcharge, pkg_rate.additional_grace AS additional_grace, package_offer, 
                       id_trunk_group, pkg_trunk_group.type AS trunk_group_type
                FROM pkg_plan
                LEFT JOIN pkg_rate ON pkg_plan.id = pkg_rate.id_plan
                LEFT JOIN pkg_prefix ON pkg_rate.id_prefix = pkg_prefix.id
                LEFT JOIN pkg_trunk_group ON pkg_trunk_group.id = pkg_rate.id_trunk_group
                WHERE pkg_plan.id = :id_plan AND pkg_rate.status = 1 AND $prefixclause
                ORDER BY LENGTH(prefix) DESC LIMIT 1";

        return Yii::$app->db->createCommand($sql, [':id_plan' => $id_plan])->queryAll();
    }
}
