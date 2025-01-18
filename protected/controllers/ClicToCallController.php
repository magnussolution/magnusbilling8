<?php

/**
 * Url for customer register http://ip/billing/index.php/clicToCall?id=username .
 */

namespace app\controllers;

use Yii;
use app\components\CController;
use app\models\CallBack;
use app\models\Configuration;
use Exception;

class ClicToCallController extends CController
{

    public function init()
    {
        $this->attributeOrder = $this->instanceModel::tableName() . '.id';
        parent::init();
        if (!isset(Yii::$app->session['language'])) {
            $language = Configuration::find()->select('config_value')->where(['LIKE', 'config_key', 'base_language'])->all();

            Yii::$app->session['language'] = $language[0]->config_value;

            Yii::$app->language = Yii::$app->sourceLanguage = isset(Yii::$app->session['language']) ? Yii::$app->session['language'] : Yii::$app->language;
        }
        $startSession = strlen(session_id()) < 1 ? session_start() : null;
    }

    public function actionIndex()
    {
        $this->render('index', 'cliToCall');
    }

    public function actionAdd()
    {
        $username = $_POST['id'];
        $exten    = $_POST['ddi'] . $_POST['ddd'] . $_POST['number'];

        $model          = new CallBack;
        $model->exten   = $exten;
        $model->channel = $username;
        $model->account = $model->channel;

        try {
            $model->save();
            $errors = $model->getErrors();
            echo '<h4>' . Yii::t('zii', 'Operation was successful.') . '</h4>';
        } catch (Exception $e) {
            $errors = $e;
        }
    }
}
