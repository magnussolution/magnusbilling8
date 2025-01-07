<?php

/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/protected/vendor/autoload.php';
require __DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/protected/config/web.php';

(new yii\web\Application($config))->run();
