<?php

/**
 * Actions of module "GroupUser".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\MagnusLog;
use app\models\GroupUser;
use app\models\GroupModule;
use app\models\GroupUserGroup;
use Exception;

class GroupUserController extends CController
{
    public $attributeOrder          = 'id';
    public $titleReport             = 'GroupUser';
    public $subTitleReport          = 'GroupUser';
    public $nameModelRelated        = 'GroupModule';
    public $extraFieldsRelated      = ['show_menu', 'action', 'id_module', 'createShortCut', 'createQuickStart'];
    public $extraValuesOtherRelated = ['idModule' => 'text'];
    public $nameFkRelated           = 'id_group';
    public $nameOtherFkRelated      = 'id_module';
    public $extraValues             = ['idUserType' => 'name'];

    public $filterByUser = false;

    public function init()
    {
        $this->instanceModel        = new GroupUser;
        $this->abstractModel        = GroupUser::find();
        $this->abstractModelRelated = GroupModule::find();
        $this->instanceModelRelated = new GroupModule;
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
    }

    public function extraFilterCustomAdmin($filter)
    {

        $modelGroupUserGroup = GroupUserGroup::find()
            ->where(['id_group_user' => Yii::$app->session['id_group']])
            ->one();

        if (isset($modelGroupUserGroup->id)) {
            $filter .= ' AND t.id IN (SELECT id_group FROM pkg_group_user_group WHERE id_group_user = ' . Yii::$app->session['id_group'] . ') ';
        }
        return $filter;
    }

    public function actionGetUserType()
    {
        $filter       = isset($_POST['filter']) ? $_POST['filter'] : null;
        $this->filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

        $query = $this->abstractModel;
        $query->where = $this->filter;
        $query->params = $this->paramsFilter;
        $modelGroupUser = $query->one();


        echo json_encode([
            $this->nameRoot => isset($modelGroupUser->id_user_type) && $modelGroupUser->id_user_type == 1 ? true : false,
        ]);
    }

    public function actionIndex()
    {
        $filter       = isset($_POST['filter']) ? $_POST['filter'] : null;
        $this->filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;
        $modelGroupUser = $this->abstractModel->where($this->filter, $this->paramsFilter)->all();
        $ids = [];
        foreach ($modelGroupUser as $value) {
            $ids[] = $value->id;
        }

        echo json_encode([
            $this->nameRoot => $ids,
        ]);
    }

    public function actionClone()
    {
        if (! Yii::$app->session['isAdmin']) {
            exit;
        }

        $success          = false;
        $this->msgSuccess = 'invalid group';
        if (isset($_POST['id'])) {
            $modelGroupUser = $this->abstractModel->query('id = :key', [':key' => (int) $_POST['id']])->one();
            if (isset($modelGroupUser->id)) {
                $this->instanceModel->name         = $modelGroupUser->name . ' Cloned';
                $this->instanceModel->id_user_type = $modelGroupUser->id_user_type;
                $this->instanceModel->save();
                $newGroupId = $this->instanceModel->id;

                $query = $this->abstractModelRelated;
                $query->where = (['=', 'id_group', $modelGroupUser->id]);
                $modelGroupModule = $query->all();

                foreach ($modelGroupModule as $groupModule) {
                    $modelGroupModuleNew             = new GroupModule();
                    $modelGroupModuleNew->attributes = $groupModule->getAttributes();
                    $modelGroupModuleNew->id_group   = $newGroupId;

                    try {
                        $success = $modelGroupModuleNew->save();
                    } catch (Exception $e) {
                        $this->msgSuccess = $this->getErrorMySql($e);
                    }
                }
            }

            if ($success) {
                $info = 'Group ' . $this->instanceModel->name;
                MagnusLog::insertLOG(4, $info);
            }
        }
        echo json_encode([
            $this->nameSuccess => $success,
            $this->nameMsg     => $this->msgSuccess,
        ]);
    }
}
