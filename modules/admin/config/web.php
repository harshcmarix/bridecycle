<?php
$config = [
    'components' => [
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
            'loginUrl' => ['backend/default/login'],
            'idParam' => '_backendUser',
            'identityCookie' => [
                'name' => '_backendUser', // unique session for frontend
            ]
        ],
    ],
];

return $config;
