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
        // Used for shop logo thumbnail 
        Yii::setAlias('@shopLogoThumbAbsolutePath', Yii::getAlias('@shopLogoAbsolutePath') . '/thumbs');
        Yii::setAlias('@shopLogoThumbRelativePath', Yii::getAlias('@shopLogoRelativePath') . '/thumbs');
        // Used for shop cover picture
        Yii::setAlias('@shopCoverPictureAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/shop_cover_picture');
        Yii::setAlias('@shopCoverPictureRelativePath', Yii::getAlias('@uploadsRelativePath') . '/shop_cover_picture');
        // Used for shop cover picture thumbnail 
        Yii::setAlias('@shopCoverPictureThumbAbsolutePath', Yii::getAlias('@shopCoverPictureAbsolutePath') . '/thumbs');
        Yii::setAlias('@shopCoverPictureThumbRelativePath', Yii::getAlias('@shopCoverPictureRelativePath') . '/thumbs');
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

        // Used for product receipt image
        Yii::setAlias('@productReceiptImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/receipts');
        Yii::setAlias('@productReceiptImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/receipts');
        // Used for product receipt image thumbnail
        Yii::setAlias('@productReceiptImageThumbAbsolutePath', Yii::getAlias('@productReceiptImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@productReceiptImageThumbRelativePath', Yii::getAlias('@productReceiptImageRelativePath') . '/thumbs');

        // Used for brand image
        Yii::setAlias('@brandImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/brand_images');
        Yii::setAlias('@brandImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/brand_images');
        // Used for brand image thumbnail
        Yii::setAlias('@brandImageThumbAbsolutePath', Yii::getAlias('@brandImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@brandImageThumbRelativePath', Yii::getAlias('@brandImageRelativePath') . '/thumbs');

        // Used for banner image
        Yii::setAlias('@bannerImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/banner_images');
        Yii::setAlias('@bannerImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/banner_images');

        // Used for banner image thumbnail
        Yii::setAlias('@bannerImageThumbAbsolutePath', Yii::getAlias('@bannerImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@bannerImageThumbRelativePath', Yii::getAlias('@bannerImageRelativePath') . '/thumbs');

        // Used for tailor shop image
        Yii::setAlias('@tailorShopImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/tailor_images');
        Yii::setAlias('@tailorShopImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/tailor_images');

        // Used for tailor image thumbnail
        Yii::setAlias('@tailorShopImageThumbAbsolutePath', Yii::getAlias('@tailorShopImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@tailorShopImageThumbRelativePath', Yii::getAlias('@tailorShopImageRelativePath') . '/thumbs');


        // Used for tailor voucher image
        Yii::setAlias('@tailorVoucherImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/tailor_voucher');
        Yii::setAlias('@tailorVoucherImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/tailor_voucher');
        // Used for tailor voucher image thumbnail
        Yii::setAlias('@tailorVoucherImageThumbAbsolutePath', Yii::getAlias('@tailorVoucherImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@tailorVoucherImageThumbRelativePath', Yii::getAlias('@tailorVoucherImageRelativePath') . '/thumbs');



        // Used for order invoice
        Yii::setAlias('@orderInvoiceAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/invoices');
        Yii::setAlias('@orderInvoiceRelativePath', Yii::getAlias('@uploadsRelativePath') . '/invoices');

        // Used for chat media/image
        Yii::setAlias('@chatMediaAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/chat_media');
        Yii::setAlias('@chatMediaRelativePath', Yii::getAlias('@uploadsRelativePath') . '/chat_media');
        // Used for chat media/image thumbnail
        Yii::setAlias('@chatMediaThumbAbsolutePath', Yii::getAlias('@chatMediaAbsolutePath') . '/thumbs');
        Yii::setAlias('@chatMediaThumbRelativePath', Yii::getAlias('@chatMediaRelativePath') . '/thumbs');

        // Used for DressType image
        Yii::setAlias('@dressTypeImageAbsolutePath', Yii::getAlias('@uploadsAbsolutePath') . '/dress_type');
        Yii::setAlias('@dressTypeImageRelativePath', Yii::getAlias('@uploadsRelativePath') . '/dress_type');
        // Used for DressType image thumbnail
        Yii::setAlias('@dressTypeImageThumbAbsolutePath', Yii::getAlias('@dressTypeImageAbsolutePath') . '/thumbs');
        Yii::setAlias('@dressTypeImageThumbRelativePath', Yii::getAlias('@dressTypeImageRelativePath') . '/thumbs');
    }
}
