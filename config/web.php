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
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
            'modules' => ['v1' => [
                'class' => 'app\modules\api\v1\Module',
            ]],
        ],
        'gridview' => ['class' => 'kartik\grid\Module'],

    ],
    'components' => [
        'session' => [
            'cookieParams' => [
                'httpOnly' => true,
                'secure' => true
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
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
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
                'host' => 'smtp.mailtrap.io',
                'username' => 'a98ec99f12d3e1',
                'password' => '36e2b0736f2086',
                'port' => 2525,
                'encryption' => 'tls',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin' => '/admin/site/index',
                'api' => '/api/v1/site/index',
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/user'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/user-address'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/search-history'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/cart-item'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/order'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/favourite-product'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/tailor'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/cms-page'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product-rating'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/banner'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/brand'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product-category'], 'pluralize' => false],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/product'], 'pluralize' => false],
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
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/<action>/',
//                    'verb' => 'PUT'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/<action>/',
//                    'verb' => 'PATCH'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/delete',
//                    'verb' => 'DELETE'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/<action>/',
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
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>',
//                    'route' => '<module>/<version>/<controller>/index',
//                    'verb' => 'GET'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>',
//                    'route' => '<module>/<version>/<controller>/index',
//                    'verb' => 'HEAD'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/<action>/',
//                    'verb' => 'GET'
//                ],
//                [
//                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
//                    'route' => '<module>/<version>/<controller>/<action>/',
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

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
