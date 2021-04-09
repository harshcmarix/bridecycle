<?php

$config = [
    'class' => 'yii\db\Connection',
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 600,
    'schemaCache' => 'cache',
    'enableQueryCache' => true,
    'queryCacheDuration' => 3600,
];

if (YII_ENV == 'dev') {
    $config['dsn'] = 'mysql:host=192.168.1.189;dbname=bridecycle';
    $config['username'] = 'bridecycle';
    $config['password'] = 'bridecycle@20221#!!';
} else if (YII_ENV == 'test') {
    $config['dsn'] = 'mysql:host=localhost;dbname=bridecycle';
    $config['username'] = 'root';
    $config['password'] = 'admin';
} else if (YII_ENV == 'prod') {
    $config['dsn'] = 'mysql:host=localhost;dbname=bridecycle';
    $config['username'] = 'root';
    $config['password'] = 'admin';
}

return $config;