<?php

/**
 * Acoes do modulo "QueueMember".
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
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\AsteriskAccess;
use app\models\QueueMember;
use app\models\Sip;
use app\models\Queue;

class QueueMemberController extends CController
{
    public $attributeOrder;
    public $extraValues    = ['idUser' => 'username'];

    public $fieldsFkReport = [
        'id_user' => [
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ],
    ];

    public function init()
    {
        $this->instanceModel = new QueueMember;
        $this->abstractModel = QueueMember::find();
        $this->titleReport   = Yii::t('zii', 'Queue Member');
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function beforeSave($values)
    {
        $this->checkRelation($values);

        if (isset($values['interface'])) {
            $modelSip = Sip::findOne(['id' => $values['interface']]);

            $values['id_user']   = $modelSip->id_user;
            $values['interface'] = 'SIP/' . $modelSip->name;
        }
        if (isset($values['queue_name'])) {
            $modelQueue = Queue::find()
                ->where(['id' => $values['queue_name']])
                ->orWhere(['name' => $values['queue_name']])
                ->one();
            $values['queue_name'] = $modelQueue->name;
        }

        return $values;
    }

    public function checkRelation($values)
    {

        if ($this->isNewRecord) {

            $modelSip   = Sip::findOne((int) $values['interface']);
            $modelQueue = Queue::findOne((int) $values['queue_name']);

            if ($modelSip->id_user != $modelQueue->id_user) {
                echo json_encode([
                    'success' => false,
                    'rows'    => [],
                    'errors'  => ['interface' => ['The SIP ACCOUNT must belong to the QUEUE owner']],
                ]);
                exit;
            }
        } else {
            if (isset($values['id']) && isset($values['interface'])) {

                $modelQueueMember = QueueMember::findOne((int) $values['id']);

                $modelSip   = Sip::findOne((int) $values['interface']);
                $modelQueue = Queue::find()
                    ->where(['name' => $modelQueueMember['queue_name']])
                    ->one();

                if ($modelSip->id_user != $modelQueue->id_user) {
                    echo json_encode([
                        'success' => false,
                        'rows'    => [],
                        'errors'  => ['interface' => ['The SIP ACCOUNT must belong to the QUEUE owner']],
                    ]);
                    exit;
                }
            }
        }
    }

    public function afterSave($model, $values)
    {
        AsteriskAccess::instance()->generateQueueFile();
    }
    public function afterUpdateAll($strIds)
    {
        AsteriskAccess::instance()->generateQueueFile();
        return;
    }

    public function afterDestroy($values)
    {
        AsteriskAccess::instance()->generateQueueFile();
    }
}
