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

        Yii::setAlias('@uploadsAbsolutePath', Yii::$app->request->baseUrl . '/uploads');
        Yii::setAlias('@uploadsRelativePath', Yii::getAlias('@webroot') . '/uploads');

        Yii::setAlias('@profilePictureAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/profile_pictures');
        Yii::setAlias('@profilePictureRelativePath', Yii::getAlias('@uploadsRelativePath') . '/profile_pictures');

        Yii::setAlias('@shopLogoAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/shop_logos');
        Yii::setAlias('@shopLogoRelativePath', Yii::getAlias('@uploadsRelativePath') . '/shop_logos');

        Yii::setAlias('@profilePictureThumbAbsolutePath', Yii::getAlias('@profilePictureAbsolutePath') . '/thumbs');
        Yii::setAlias('@profilePictureThumbRelativePath', Yii::getAlias('@profilePictureRelativePath') . '/thumbs');

    }
}
