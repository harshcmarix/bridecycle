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
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.mailtrap.io',
                'username' => '1d307e3736a892',
                'password' => '07ac69fb9b8f36',
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
                //  ['class' => 'yii\rest\UrlRule', 'controller' => ['api/v1/user'], 'pluralize' => false],
                [
                    'pattern' => 'admin/<controller:\w+>/<action:[\w-]+>',
                    'route' => 'admin/<controller>/<action>',
                ],
                // Set rest API rules
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>',
                    'route' => '<module>/<version>/<controller>/create',
                    'verb' => 'POST'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/<action>',
                    'verb' => 'POST'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/update',
                    'verb' => 'PUT'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/update',
                    'verb' => 'PATCH'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/<action>/',
                    'verb' => 'PUT'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/<action>/',
                    'verb' => 'PATCH'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/delete',
                    'verb' => 'DELETE'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/<action>/',
                    'verb' => 'DELETE'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/view',
                    'verb' => 'GET'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/view',
                    'verb' => 'HEAD'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>',
                    'route' => '<module>/<version>/<controller>/index',
                    'verb' => 'GET'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>',
                    'route' => '<module>/<version>/<controller>/index',
                    'verb' => 'HEAD'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/<action>/',
                    'verb' => 'GET'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/<action>/',
                    'verb' => 'HEAD'
                ],
                [
                    'pattern' => '<module:[\w-]+>/<version:[\w-]+>/<controller:[\w-]+>/<id:\d+>',
                    'route' => '<module>/<version>/<controller>/options',
                    'verb' => 'OPTIONS'
                ],
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
