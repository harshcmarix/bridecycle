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
        // user profile picture
        Yii::setAlias('@profilePictureAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/profile_pictures');
        Yii::setAlias('@profilePictureRelativePath', Yii::getAlias('@uploadsRelativePath') . '/profile_pictures');

        Yii::setAlias('@profilePictureThumbAbsolutePath', Yii::getAlias('@profilePictureAbsolutePath') . '/thumbs');
        Yii::setAlias('@profilePictureThumbRelativePath', Yii::getAlias('@profilePictureRelativePath') . '/thumbs');
        // Product Categories
        Yii::setAlias('@productCategoryImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/product_images');
        Yii::setAlias('@productCategoryImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/product_images');

        Yii::setAlias('@productCategoryImageThumbAbsolutePath', Yii::getAlias('@productCategoryImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@productCategoryImageThumbRelativePath', Yii::getAlias('@productCategoryImageRelativePath') . '/thumbs');

    }
}
