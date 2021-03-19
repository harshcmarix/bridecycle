<?php

/**
 * Developer Debugging function
 * @param $value
 * @param int $exit
 */
function p($value, $exit = 1)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    if ($exit == 1) {
        die;
    }
}

if (empty(getenv('ENVIRONMENT'))) {
    exit('Undefined environment');
}

defined('YII_ENV') or define('YII_ENV', getenv('ENVIRONMENT'));

if (YII_ENV == 'dev') {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
