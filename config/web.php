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


//        'imdhemy' => [
//            'class' => 'Imdhemy\GooglePlay\ClientFactory',
//            'privateKeyData' => '{
//  "type": "service_account",
//  "project_id": "bride-cycle-cf380",
//  "private_key_id": "0d77c6197bc079a0c3a8e79e339c50449ffeb37a",
//  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQC16x1e6AaKE6I1\na56TNxXTIX7aXE/BkOXqUGWE0ACKzBTZFgytnTgeZibAf1dJIM2oMlmlnyzxMG6J\n931EtjeeYX4Cw916WB/OzvG/SZKpFHhyMUfdRhPOFSPvjN7qMNwoGmQe6nm2AWBU\nTaccTzaE2bh2vxV8c0n609/aOwdMbLUSVyH/xx+TOJ7HJO+ajadek4G+o+ur7NcN\nV4h6IBscfgU+BRjpv2dI1LS5pDlfHQd1Ur8anbUyFl+FFW1NBBDG0rlTMckeN3Ra\nk0HGjp5QcjJ88i+yvG6BOVrLSm+IwMq2S4U+Ur1gsC3L/zeWLE+pKe9oCB9SEm4n\nKDDbNj2BAgMBAAECgf9q6TVM0Vw5r9AxSBrctnYQRPi7QgUDsDEIKeqyyVfuoIFs\n3/utDJIufo1hRw7FEcIxvWVx/SbF/oorPiEggI+vRLYJnSvlx5e2sptGqYMt9IA7\nAm55j1v6tFhDyayeQjJTkmIrL4gLFRDHrdsuVMWozbGnsVgVpRH7lwjqxKx4lje/\nd0Y6kIAgfLO7GEGLfXQ9G6/GJzKwPZBq9tjCxQT9LxpkgkXvGscN+y7XO8MCkFWL\nP5YZNENU1pLs74eB7A4xdwZNFuD78uRhOROoH+8GkU/DGjuYQnJ6kH1qL4ggp3gO\nK1dtBNGZI4QggRpH0bMpVZH8MQXVlYVlW/E7NwECgYEA804lmrc0oNgnL6lXAhES\nNhEqn9Px30/2AqizkL8TGSJHKETFPFVWDxdZcpyKm4MIpvDFR+9GL6+3QkR6p3hm\nos5JtU6jPtGaiSqn1Wx0kjiCt42g49svimCppkoekOYBC1pBIBhtkMpFd9PKBVcd\nqD6p0B+27IbFA65R+Ow56eECgYEAv2kEe/omOdUJukt/KvsnN5IMBOoIR6xMKJs2\nXMLD38LoWXuRmEYtbgrtHScGuZs04wOwYZCZ3Oi37T63b5KtUTA6CGodwsBFqz6y\nlHVS+xPDr82EhZ1etRlPWrKO6A6RIXA5XRLnvFcw6x3c1bUGAmXDXXEMsnPUmIiG\nWaQFB6ECgYEAoqL6QBF2/I2ApDtuDdObeMjA1VW3uK6ao+xhG8Cd448mQaS2sz3X\n6P6SrCo3/Pv6LJ2FQjCPmhC7T3C3DHtPIEUEwE1wke1Kf6YjymZVBekAd/IUOjup\n67XxsVaaWAFncU9DeVDa7I5JIXBQ+oDhMjWxDY4lmLX9vBgqc1p03AECgYBLxHBH\n+w8imVfObJAcHWq6ro8fatx4MpW7BSWRtm7+phJ/SVCqrCzTgHO3tHkPA4b9zboG\nBM1DCsOzn73Q5ojAuIxgRFEfhCiPzrO13HNKe9pcPsgUc+THXwtwZXn4FbwRNnEe\njJs6jHEKwLlnxs5oKIwa8uMJyJwA/RnhZfGvwQKBgQC/BSnC6FZucNBbUaugqbG6\n2l6h/1K/vnigLZpozDxGb2lDBq2VxevUyT49M6I2F4Xs1nvVLPLPe587DLw3dNmD\nbGYY8oTpPMZOjJVrHlT7gkLai+BhqA2TNcQre+hHi+lMaUZ5pj/q0WpVG1JTUgRh\nqCuHe90Ysw7jIs9IrpzmZA==\n-----END PRIVATE KEY-----\n",
//  "client_email": "firebase-adminsdk-47r2q@bride-cycle-cf380.iam.gserviceaccount.com",
//  "client_id": "101515763135510528805",
//  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
//  "token_uri": "https://oauth2.googleapis.com/token",
//  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
//  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-47r2q%40bride-cycle-cf380.iam.gserviceaccount.com"
//}
//',
//        ],


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
            //'apiKey' => 'AAAApcG5M1M:APA91bFB1x-W2c0hTxn1HpQH9U1ROkK9-3-ieG-NlBm852LIn0pGiaFi0FUsZM09GzbbQCCq3vGCXB8lHEjW0x-jh-pSEMnThSuXMpszfBoX52zoIVJ6LOQFy6RxwHS3n56wfXYhf87w', // Get it from https://console.firebase.google.com/project/bridecycle-test/settings/cloudmessaging
            'apiKey' => 'AAAApcG5M1M:APA91bFB1x-W2c0hTxn1HpQH9U1ROkK9-3-ieG-NlBm852LIn0pGiaFi0FUsZM09GzbbQCCq3vGCXB8lHEjW0x-jh-pSEMnThSuXMpszfBoX52zoIVJ6LOQFy6RxwHS3n56wfXYhf87w', // Get it from https://console.firebase.google.com/project/bride-cycle-cf380/settings/cloudmessaging/ios:com.bridecycle.ios
        ],
        // Logging
        'log' => [
            //'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                // writes to php-fpm output stream
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace', 'error', 'warning'],
                    'categories' => ['notifyUserBasedOnsaveSearch'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/addproductnotification/' . date('d-m-Y') . 'addProductTonotifyUserBasedOnsaveSearch.log',
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
