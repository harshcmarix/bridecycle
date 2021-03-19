<?php

namespace app\components;

use Yii;
use yii\base\Component;

/**
 * Class Aliases
 * @package app\components
 */
class Aliases extends Component
{

    public function init()
    {
        Yii::setAlias('@webroot', Yii::getAlias('@webroot'));
        Yii::setAlias('@css', Yii::getAlias('@webroot') . '/css');
        Yii::setAlias('@js', Yii::getAlias('@webroot') . '/js');

//        Yii::setAlias('@uploadsAbsolutePath', Yii::$app->request->baseUrl . '/uploads');
//        Yii::setAlias('@uploadsRelativePath', Yii::getAlias('@webroot') . '/uploads');

    }
}
