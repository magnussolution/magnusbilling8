<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * MagnusSolution.com <info@magnussolution.com>
 *
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/protected/vendor/autoload.php';
require __DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/protected/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
