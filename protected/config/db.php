<?php

$configFile = '/etc/asterisk/res_config_mysql.conf';
$array      = parse_ini_file($configFile);

return [
    'class'    => 'yii\db\Connection',
    'dsn'      => 'mysql:host=' . $array['dbhost'] . ';dbname=' . $array['dbname'],
    'username' => $array['dbuser'],
    'password' => $array['dbpass'],
    'charset'  => 'utf8',
    'attributes'       => [
        PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    ],
    'enableSchemaCache' => false,
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
