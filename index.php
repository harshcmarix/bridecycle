<?php

//if (empty(getenv('ENVIRONMENT'))) {
//    exit('Undefined environment');
//}





defined('YII_ENV') or define('YII_ENV', getenv('ENVIRONMENT'));
if (YII_ENV) {
    if (YII_ENV == 'dev') {
        header("location: /bridecycle/web/admin");
    } else if (YII_ENV == 'prod') {
        header("location: /bridecycle/web/admin");
    } else {
        header("location: /bridecycle/web/admin");
    }
} else {
    header("location: /bridecycle/web/admin");
}
header("location: /bridecycle/web/admin");



// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// comment out the following two lines when deployed to production
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/./vendor/autoload.php';
require __DIR__ . '/./vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/./config/web.php';

(new yii\web\Application($config))->run();
