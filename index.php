<?php

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


