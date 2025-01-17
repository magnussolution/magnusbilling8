<?php

/**
 * Acoes do modulo "Call".
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
 * Magnusbilling.org <info@magnusbilling.org>
 * 04/01/2025
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\CallSummaryMonthDid;

class CallSummaryMonthDidController extends CController
{
    public $extraValues    = array('idDid' => 'did');
    public $fieldsFkReport = array(
        'id_did' => array(
            'table'       => 'pkg_did',
            'pk'          => 'id',
            'fieldReport' => 'did',
        ),
    );

    public function init()
    {
        $this->instanceModel = new CallSummaryMonthDid;
        $this->abstractModel = CallSummaryMonthDid::find();
        $this->titleReport   = Yii::t('zii', 'Summary Month DID');
        $this->attributeOrder = $this->instanceModel::tableName() . '.month DESC';
        parent::init();
    }

    public function recordsExtraSum($records = array())
    {
        foreach ($records as $key => $value) {
            $records[0]->sumsessiontime += $value['sessiontime'] / 60;
            $records[0]->sumsessionbill += $value['sessionbill'];
            $records[0]->sumaloc_all_calls += $value['sessiontime'] / $value['nbcall'];
            $records[0]->sumnbcall += $value['nbcall'];
        }

        $this->nameSum = 'sum';

        return $records;
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item) {
            $attributes[$key]                      = $item->attributes;
            $attributes[$key]['nbcall']            = $item->nbcall;
            $attributes[$key]['aloc_all_calls']    = $item->aloc_all_calls;
            $attributes[$key]['month']             = substr($item->month, 0, 4) . '-' . substr($item->month, 4);
            $attributes[$key]['sessiontime']       = $item->sessiontime / 60;
            $attributes[$key]['sumsessiontime']    = $item->sumsessiontime;
            $attributes[$key]['sumsessionbill']    = $item->sumsessionbill;
            $attributes[$key]['sumaloc_all_calls'] = $item->sumaloc_all_calls;
            $attributes[$key]['sumnbcall']         = $item->sumnbcall;

            if (isset(Yii::$app->session['isClient']) && Yii::$app->session['isClient']) {
                foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if (isset(Yii::$app->session['isAgent']) && Yii::$app->session['isAgent']) {
                foreach ($this->fieldsInvisibleAgent as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if (Yii::$app->session['idClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if (Yii::$app->session['idAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }
}
