<?php

/**
 * Acoes do modulo "TemplateMail".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2025 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 * https://www.google.com/settings/security/lesssecureapps
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\TemplateMail;

class TemplateMailController extends CController
{

    public function init()
    {

        $this->instanceModel = new TemplateMail;
        $this->abstractModel = TemplateMail::find();
        $this->titleReport   = Yii::t('zii', 'Emails');

        if (Yii::$app->session['isAdmin']) {
            $this->relationFilter['idUser'] = [
                'condition' => "idUser.id  = 1",
            ];

            $this->attributeOrder = $this->instanceModel::tableName() . '.language, ' . $this->instanceModel::tableName() . '.mailtype';
            parent::init();
        }
    }

    public function extraFilterCustomAgent($filter)
    {
        //se é agente filtrar pelo user.id_user

        $this->relationFilter['idUser'] = [
            'condition' => "idUser.id LIKE :agfby",
        ];

        $this->paramsFilter[':agfby'] = Yii::$app->session['id_user'];

        return $filter;
    }
}
