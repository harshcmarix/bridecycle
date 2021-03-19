<?php

if (empty(getenv('ENVIRONMENT'))) {
    exit('Undefined environment');
}

defined('YII_ENV') or define('YII_ENV', getenv('ENVIRONMENT'));

if (YII_ENV == 'dev') {
    header("location: /bridecycle/web/");
} else if (YII_ENV == 'prod') {
    header("location: http://library.wibeats.it/");
} else {
    header("location: /bridecycle/web/");
}