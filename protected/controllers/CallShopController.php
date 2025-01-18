<?php

/**
 * Acoes do modulo "CallShop".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * MagnusSolution.com <info@magnussolution.com>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\models\Sip;
use app\models\CallShop;
use app\models\CallShopCdr;
use app\components\CController;

class CallShopController extends CController
{
    public $extraValues    = ['idUser' => 'username'];
    public $joinWith           = 'idUser';
    public $defaultFilter  = 'pkg_user.callshop = 1';

    public function init()
    {
        $this->instanceModel = new CallShop;
        $this->abstractModel = CallShop::find();
        $this->titleReport   = Yii::t('zii', 'CallShop');
        $this->attributeOrder = $this->instanceModel::tableName() . '.callerid';
        parent::init();
    }

    public function actionRead($asJson = true, $condition = null)
    {
        return parent::actionRead($asJson = true, $condition = null);
    }

    public function getAttributesModels($models, $itemsExtras = [])
    {

        $attributes = false;
        foreach ($models as $key => $item) {
            $attributes[$key] = $item->attributes;

            $decimal                   = strlen(Yii::$app->session['decimal']);
            $sql                       = 'SELECT SUM(price) priceSum FROM pkg_callshop t WHERE cabina = "' . $item->name . '" AND status = 0';
            $sumResult                 = Yii::$app->db->createCommand($sql)->queryAll();
            $total                     = is_numeric($sumResult[0]['priceSum']) ? number_format($sumResult[0]['priceSum'], $decimal) : '0.00';
            $attributes[$key]['total'] = $total;

            if (strlen($attributes[$key]['callshopnumber'])) {
                $sql = "SELECT * FROM pkg_rate_callshop WHERE id_user = " . Yii::$app->session['id_user'] . " AND  dialprefix = SUBSTRING(" . $attributes[$key]['callshopnumber'] . ",1,length(dialprefix))
                                ORDER BY LENGTH(dialprefix) DESC LIMIT 1";
                $command      = Yii::$app->db->createCommand($sql);
                $resultPrefix = $command->queryAll();

                $attributes[$key]['price_min']   = isset($resultPrefix[0]['buyrate']) ? $resultPrefix[0]['buyrate'] : 0;
                $attributes[$key]['destination'] = isset($resultPrefix[0]['destination']) ? $resultPrefix[0]['destination'] : '';
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if ($_SESSION['isClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if ($_SESSION['isAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    public function actionLiberar()
    {

        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $modelSip = Sip::findOne((int) $id);
            $modelSip->status = 2;
            $modelSip->save();
        } else {

            if (isset($_GET['name'])) {
                $filter[0]['value'] = $_GET['name'];
            } else {
                $filter = json_decode($_POST['filter'], true);
            }

            $modelSip = Sip::find()->where(['name' => $filter[0]['value']])->one();
            if ($modelSip !== null) {
                $modelSip->status = 2;
                $modelSip->save();
            }
        }

        echo json_encode([
            $this->nameSuccess => true,
            $this->nameMsg     => $this->msgSuccess,
        ]);
    }

    public function actionCobrar()
    {
        if (isset($_GET['id'])) {
            $id                       = (int) $_GET['id'];
            $modelSip                 = Sip::findOne((int) $id);
            $modelSip->status         = 0;
            $modelSip->callshopnumber = 'NULL';
            $modelSip->callshoptime   = 0;
            $modelSip->save();
            CallShopCdr::updateAll(['status' => '1'], ['cabina' => $modelSip->name]);
        } else {

            if (isset($_GET['name'])) {
                $filter[0]['value'] = $_GET['name'];
            } else {
                $filter = json_decode($_POST['filter'], true);
            }

            $modelSip = Sip::find()->where(['name' => $filter[0]['value']])->one();
            $modelSip->status         = 0;
            $modelSip->callshopnumber = 'NULL';
            $modelSip->callshoptime   = 0;
            $modelSip->save();

            CallShopCdr::updateAll(['status' => '1'], ['cabina' => $filter[0]['value']]);
        }
        echo json_encode([
            $this->nameSuccess => true,
            $this->nameMsg     => $this->msgSuccess,
        ]);
    }
}
