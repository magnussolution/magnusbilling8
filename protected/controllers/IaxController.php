<?php

/**
 * Acoes do modulo "Iax".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\Iax;

class IaxController extends CController
{
    public $attributeOrder = 'regseconds DESC';
    public $extraValues    = array('idUser' => 'username');

    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
    );

    public function init()
    {
        $this->instanceModel = new Iax;
        $this->abstractModel = Iax::find();
        parent::init();
    }

    public function beforeSave($values)
    {

        if ($this->isNewRecord) {

            $values['name'] = $values['username'];

            $values['regseconds'] = 1;
            $values['context']    = 'billing';
            $values['regexten']   = $values['name'];
            if (!$values['callerid']) {
                $values['callerid'] = $values['name'];
            }
        }

        if (isset($values['callerid'])) {
            $values['cid_number'] = $values['callerid'];
        }

        if (isset($value['allow'])) {
            $values['allow'] = preg_replace("/,0/", "", $values['allow']);
            $values['allow'] = preg_replace("/0,/", "", $values['allow']);
        }

        return $values;
    }

    public function afterSave($model, $values)
    {
        AsteriskAccess::instance()->generateIaxPeers();
        return;
    }

    public function afterDestroy($values)
    {
        AsteriskAccess::instance()->generateIaxPeers();
        return;
    }
}
