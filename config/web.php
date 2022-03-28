<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'Bride Cycle',
    'name' => 'Bride Cycle',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'app\components\Aliases'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],

    //'languages' => ['en-EN', 'ru-RU'],

    // set target language to be Russian
//    'language' => 'de-DE',
//
    // set source language to be English
//    'sourceLanguage' => 'en-US',

    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
            'modules' => [
                'v1' => [
                    'class' => 'app\modules\api\v1\Module',
                ],
                'v2' => [
                    'class' => 'app\modules\api\v2\Module',
                ]
            ],
        ],
        'gridview' => ['class' => 'kartik\grid\Module'],
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'session' => [
            'cookieParams' => [
                'httpOnly' => false,
                'secure' => false
            ]
        ],
        'cookies' => [
            'class' => 'yii\web\Cookie',
            'httpOnly' => true,
            'secure' => true
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'zwkfUl-Ef9OnkhaHMl7SaqQrTBSsAFU-',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
                ],
            ],
        ],
        'html2pdf' => [
            'class' => 'yii2tech\html2pdf\Manager',
            'viewPath' => '@app/pdf',
            'converter' => [
                'class' => 'yii2tech\html2pdf\converters\Wkhtmltopdf',
                'defaultOptions' => [
                    'pageSize' => 'A4'
                ],
            ]
        ],

        'formatter' => [
            'thousandSeparator' => ',',
            'currencyCode' => 'EUR',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
//                    //'sourceLanguage' => 'en-US',
//                    'fileMap' => [
//                        'app' => 'app.php',
//                        'app/error' => 'error.php',
//                    ],
                ],
                'kvgrid' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'harshil.cmarix@gmail.com',
                'password' => 'harshil#8989#?',
                'port' => '587',
                'encryption' => 'tls',
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]
            ],
        ],

        'fcm' => [
            'class' => 'understeam\fcm\Client',
            'apiKey' => 'AAAApcG5M1M:APA91bFB1x-W2c0hTxn1HpQH9U1ROkK9-3-ieG-NlBm852LIn0pGiaFi0FUsZM09GzbbQCCq3vGCXB8lHEjW0x-jh-pSEMnThSuXMpszfBoX52zoIVJ6LOQFy6RxwHS3n56wfXYhf87w', // Get it from https://console.firebase.google.com/project/bride-cycle-cf380/settings/cloudmessaging/ios:com.bridecycle.ios
        ],
        // Logging
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
//                // writes to php-fpm output stream
//                [
//                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['info', 'trace', 'error', 'warning'],
//                    'categories' => ['notifyUserBasedOnsaveSearch'],
//                    'logVars' => [],
//                    //'logFile' => '@runtime/logs/addproductnotification/' . date('d-m-Y') . 'addProductTonotifyUserBasedOnsaveSearch.log',
//                ],
//                // writes to php-fpm output stream
//                [
//                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['info', 'trace', 'error', 'warning'],
//                    'categories' => ['notifyUserBasedOnAndroidGooglePlaySubscription'],
//                    'logVars' => [],
//                    //'logFile' => '@runtime/logs/androidgoogleplaysubscriptionfail/' . date('d-m-Y') . 'androidGooglePlaySubscriptionFailUser.log',
//                ],

                [

                    'class' => 'yii\log\FileTarget',

                    'categories' => ['stripe_connect_account'],

                    'exportInterval' => 1,

                    'logFile' => '@app/runtime/logs/stripelog_' . date('Y-m-d H:i:s') . '.log',

                ],

            ],

        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin' => '/admin/site/index',
//                'api' => '/api/v1/site/index',
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/user', 'api/v2/user'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/user-address', 'api/v2/user-address'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/search-history', 'api/v2/search-history'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/cart-item', 'api/v2/cart-item'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/order', 'api/v2/order'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/favourite-product', 'api/v2/favourite-product'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/tailor', 'api/v2/tailor'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/cms-page', 'api/v2/cms-page'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product-rating', 'api/v2/product-rating'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/banner', 'api/v2/banner'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/brand', 'api/v2/brand'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product-category', 'api/v2/product-category'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product', 'api/v2/product'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/color', 'api/v2/color'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product-image', 'api/v2/product-image'], 'pluralize' => false],
                // Set rest API rules
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>',
//                    'route' => '<module>/<version>/<controller>/create',
//                    'verb' => 'POST'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/<action>',
//                    'verb' => 'POST'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/update',
//                    'verb' => 'PUT'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/update',
//                    'verb' => 'PATCH'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/delete',
//                    'verb' => 'DELETE'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/view',
//                    'verb' => 'GET'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/view',
//                    'verb' => 'HEAD'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/options',
//                    'verb' => 'OPTIONS'
//                ],
                // Set admin rules
//                [
//                    'pattern' => 'admin/<controller:\w+>/<action:[\w-]+>',
//                    'route' => 'admin/<controller>/<action>',
//                ],
            ],
        ],
    ],
    'params' => $params,
];

//if (YII_ENV_DEV) {
//    // configuration adjustments for 'dev' environment
//    $config['bootstrap'][] = 'debug';
//    $config['modules']['debug'] = [
//        'class' => 'yii\debug\Module',
//        // uncomment the following to add your IP if you are not connecting from localhost.
//        //'allowedIPs' => ['127.0.0.1', '::1'],
//    ];
//
//    $config['bootstrap'][] = 'gii';
//    $config['modules']['gii'] = [
//        'class' => 'yii\gii\Module',
//        // uncomment the following to add your IP if you are not connecting from localhost.
//        //'allowedIPs' => ['127.0.0.1', '::1'],
//    ];
//}

$config['bootstrap'][] = 'debug';
$config['modules']['debug'] = [
    'class' => 'yii\debug\Module',
];
return $config;
