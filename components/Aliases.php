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
    /**
     * Initialize aliases
     */
    public function init()
    {
        // Used for css & js file path
        Yii::setAlias('@webroot', Yii::getAlias('@webroot'));
        Yii::setAlias('@css', Yii::getAlias('@webroot') . '/css');
        Yii::setAlias('@js', Yii::getAlias('@webroot') . '/js');
        // Used for upload directory
        Yii::setAlias('@uploadsAbsolutePath', Yii::$app->request->baseUrl . '/uploads');
        Yii::setAlias('@uploadsRelativePath', Yii::getAlias('@webroot') . '/uploads');
        // Used for profile picture
        Yii::setAlias('@profilePictureAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/profile_pictures');
        Yii::setAlias('@profilePictureRelativePath', Yii::getAlias('@uploadsRelativePath') . '/profile_pictures');
        // Used for profile picture thumbnail
        Yii::setAlias('@profilePictureThumbAbsolutePath', Yii::getAlias('@profilePictureAbsolutePath') . '/thumbs');
        Yii::setAlias('@profilePictureThumbRelativePath', Yii::getAlias('@profilePictureRelativePath') . '/thumbs');
        // Used for shop logo
        Yii::setAlias('@shopLogoAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/shop_logos');
        Yii::setAlias('@shopLogoRelativePath', Yii::getAlias('@uploadsRelativePath') . '/shop_logos');
        // Used for product category image
        Yii::setAlias('@productCategoryImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/product_images');
        Yii::setAlias('@productCategoryImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/product_images');
        // Used for product category image thumbnail
        Yii::setAlias('@productCategoryImageThumbAbsolutePath', Yii::getAlias('@productCategoryImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@productCategoryImageThumbRelativePath', Yii::getAlias('@productCategoryImageRelativePath') . '/thumbs');

        // Used for product image
        Yii::setAlias('@productImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/product_images');
        Yii::setAlias('@productImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/product_images');
        // Used for product image thumbnail
        Yii::setAlias('@productImageThumbAbsolutePath', Yii::getAlias('@productImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@productImageThumbRelativePath', Yii::getAlias('@productImageRelativePath') . '/thumbs');

        // Used for brand image
        Yii::setAlias('@brandImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/brand_images');
        Yii::setAlias('@brandImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/brand_images');
        // Used for brand image thumbnail
        Yii::setAlias('@brandImageThumbAbsolutePath', Yii::getAlias('@brandImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@brandImageThumbRelativePath', Yii::getAlias('@brandImageRelativePath') . '/thumbs');
    }
}
