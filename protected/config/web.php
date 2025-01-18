<?php


$params = require __DIR__ . '/params.php';
$db     = require __DIR__ . '/db.php';

$config = [
    'id'         => 'basic',
    'basePath'   => dirname(__DIR__),
    'name'       => 'MagnusBilling',
    'bootstrap'  => ['log'],
    'language' => 'zh',
    'aliases'    => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],


    'components' => [
        'urlManager'   => [
            'class'           => 'yii\web\UrlManager',
            'enablePrettyUrl' => true, // Habilita URLs amigáveis
            'showScriptName'  => false, // Oculta o "index.php" na URL
            'rules'           => [
                // Regra para URL no formato controller/view/id
                '<controller:\w+>/<id:\d+>'              => '<controller>/view',

                // Regra para URL no formato controller/action/id
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',

                // Regra para URL no formato controller/action
                '<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
            ],
        ],
        'request'      => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'MVBUS23m2KShubnsKo8796eTg6aPa',
            'enableCsrfValidation' => false,
        ],
        'cache'        => [
            'class' => 'yii\caching\FileCache',

        ],
        'user'         => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer'       => [
            'class'            => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '/Library/WebServer/Documents/html/mbilling_8_dev/resources/locale/php',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'app' => 'zii.php',
                    ],
                ],
            ],
        ],
        'db'           => $db,
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params'     => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][]      = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
