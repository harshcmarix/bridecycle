<?php

namespace app\modules\admin;

use yii\helpers\Url;
use \yii\base\Module as BaseModule;

/**
 * Class Module
 * @package app\modules\admin
 */
class Module extends BaseModule
{
    /**
     * @var string
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * Set default layout for module
     * @var string
     */
    public $layout = 'main';

    /**
     * Set custom options
     */
    public function init()
    {
        \Yii::$app->getUser()->setReturnUrl(Url::toRoute(['/admin/site/index']));
        \Yii::$app->setHomeUrl(Url::toRoute(['/admin/site/index']));

        parent::init();

        \Yii::configure($this, require(__DIR__ . '/config/web.php'));
    }
}
