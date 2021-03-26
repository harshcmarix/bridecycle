<?php

namespace app\modules\api\v1;

/**
 * Class Module
 * @package app\modules\api\v1
 */
class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'app\modules\api\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here

        \Yii::$app->set('user', [
            'class' => 'yii\web\User',
            'identityClass' => 'app\modules\api\v1\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => null,
            'identityCookie' => ['name' => '_apiUser', 'httpOnly' => true],
        ]);
    }
}
