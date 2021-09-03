<?php

//if (empty(getenv('ENVIRONMENT'))) {
//    exit('Undefined environment');
//}
p("fdsf");
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
