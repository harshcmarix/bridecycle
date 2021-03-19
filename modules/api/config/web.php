<?php
$config = [
    'components' => [
        'session' => [
            'class' => 'yii\web\Session',
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
            'class' => 'yii\web\Request',
            'cookieValidationKey' => 'zwkfUl-Ef9OnkhaHMl7SaqQrTBSsAFU-',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'POST <module:[\w-]+>/<controller:[\w-]+>' => '<module>/<controller>/create',
                'PUT,PATCH <module:[\w-]+>/<controller:[\w-]+>/<id:\d+>' => '<module>/<controller>/update',
                'PUT,PATCH <module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<module>/<controller>/<action>/',
                'DELETE <module:[\w-]+>/<controller:[\w-]+>/<id:\d+>' => '<module>/<controller>/delete',
                'DELETE <module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<module>/<controller>/<action>/',
                'GET,HEAD <module:[\w-]+>/<controller:[\w-]+>/<id:\d+>' => '<module>/<controller>/view',
                'GET,HEAD <module:[\w-]+>/<controller:[\w-]+>' => '<module>/<controller>/index',
                'GET,HEAD <module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<module>/<controller>/<action>/',
                'OPTIONS <module:[\w-]+>/<controller:[\w-]+>/<id:\d+>' => '<module>/<controller>/options',
                'POST,OPTIONS <module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<module>/<controller>/<action>',
            ],
        ],
    ],
];

return $config;
