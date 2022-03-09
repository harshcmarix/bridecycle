<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property int $number
 * @property int|null $category_id
 * @property int $sub_category_id
 * @property int $price
 * @property string|null $option_size
 * @property float|null $option_price
 * @property double|null $refer_price
 * @property string|null $option_conditions
 * @property string|null $option_show_only
 * @property string|null $description
 * @property int|null $available_quantity
 * @property string $is_top_selling 1 => top selling product
 * @property string $is_top_trending 1 => top trending product
 * @property string $is_admin_favourite 1 => admin favourite product
 * @property int $dress_type_id
 * @property int $brand_id
 * @property string|null $gender 1 => female
 * @property string|null $type n => new , u => used
 * @property string|null $product_tracking_id
 * @property string $is_cleaned 1 => cleaned product
 * @property string $is_receipt 1 => cleaned product receipt
 * @property int|null $height
 * @property int|null $weight
 * @property int|null $width
 * @property int $status_id
 * @property int $address_id
 * @property string|null $option_color
 * @property int|null $is_saved_search_notification_sent 1 => yes,0 => no
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FavouriteProduct[] $favouriteProducts
 * @property OrderItem[] $orderItems
 * @property ProductRating[] $productRatings
 * @property ProductImage[] $productImages
 * @property ProductImage[] $productImages0
 * @property ProductSizes[] $ProductSizes
 * @property Brand $brand
 * @property Brand $brand0
 * @property Color $color
 * @property ProductCategory $category
 * @property ProductCategory $category0
 * @property ProductCategory $subCategory
 * @property UserAddress $address
 * @property ProductReceipt $productReceipt
 * @property ProductReceipt $productReceipt0
 *
 */
class Product extends \yii\db\ActiveRecord
{

    /**
     * used for create
     */
    const SCENARIO_CREATE = 'create';

    /**
     * used to check image empty or not
     */
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    public $is_product_images_empty;

    public $receipt;
    public $is_profile_address;
    public $is_product_receipt_images_empty;

    /**
     * @var
     *
     *  It is use for admin panel only
     */
    public $shipping_country_ids;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d h:i:s'),
            ],
        ];
    }

    public $images;
    public $shipping_country_id;
    public $shipping_country;
    public $shipping_country_price;
    public $refer_price;

    const IS_TOP_SELLING_YES = '1';
    const IS_TOP_SELLING_NO = '0';

    const IS_TOP_TRENDING_YES = '1';
    const IS_TOP_TRENDING_NO = '0';

    const OPTION_IS_SHOW_ONLY_YES = '1';
    const OPTION_IS_SHOW_ONLY_NO = '0';

    const IS_CLEANED_YES = '1';
    const IS_CLEANED_NO = '0';

    const IS_ADMIN_FAVOURITE_YES = '1';
    const IS_ADMIN_FAVOURITE_NO = '0';

    const GENDER_FOR_FEMALE = '1';
    const GENDER_FOR_MALE = '0';
    const GENDER_FOR_ALL = '3';

    const PRODUCT_TYPE_NEW = 'n';
    const PRODUCT_TYPE_USED = 'u';

    public $arrIsTopSelling = [
        self::IS_TOP_SELLING_YES => 'Yes',
        self::IS_TOP_SELLING_NO => 'No',
    ];

    public $arrIsTopTrending = [
        self::IS_TOP_TRENDING_YES => 'Yes',
        self::IS_TOP_TRENDING_NO => 'No',
    ];

    public $arrOptionIsShowOnly = [
        self::OPTION_IS_SHOW_ONLY_YES => 'Yes',
        self::OPTION_IS_SHOW_ONLY_NO => 'No',
    ];

    public $arrGender = [
        self::GENDER_FOR_FEMALE => 'Female',
        //self::GENDER_FOR_MALE => 'Male',
        // self::GENDER_FOR_ALL => 'All',
    ];

    public $arrIsCleaned = [
        self::IS_CLEANED_YES => 'Yes',
        self::IS_CLEANED_NO => 'No',
    ];

    public $arrProductType = [
        self::PRODUCT_TYPE_NEW => 'New',
        self::PRODUCT_TYPE_USED => 'Used',
    ];

    const IS_SAVED_SEARCH_NOTIFICATION_SENT_TRUE = '1';
    const IS_SAVED_SEARCH_NOTIFICATION_SENT_FALSE = '0';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required', 'message' => getValidationErrorMsg('name_required', Yii::$app->language)], //'is_top_selling', 'is_top_trending', 'number'
            [['category_id'], 'required', 'message' => getValidationErrorMsg('category_id_required', Yii::$app->language)],
            [['price'], 'required', 'message' => getValidationErrorMsg('price_required', Yii::$app->language)],
            [['available_quantity'], 'required', 'message' => getValidationErrorMsg('available_quantity_required', Yii::$app->language)],
            [['gender'], 'required', 'message' => getValidationErrorMsg('gender_required', Yii::$app->language)],
            [['is_cleaned'], 'required', 'message' => getValidationErrorMsg('is_cleaned_required', Yii::$app->language)],
            [['status_id'], 'required', 'message' => getValidationErrorMsg('status_required', Yii::$app->language)],
            [['option_color'], 'required', 'message' => getValidationErrorMsg('option_color_required', Yii::$app->language)],

            [['category_id'], 'integer', 'message' => getValidationErrorMsg('category_id_integer_validation', Yii::$app->language)],
            [['sub_category_id'], 'integer', 'message' => getValidationErrorMsg('sub_category_id_integer_validation', Yii::$app->language)],
            [['available_quantity'], 'integer', 'message' => getValidationErrorMsg('available_quantity_integer_validation', Yii::$app->language)],
            [['brand_id'], 'integer', 'message' => getValidationErrorMsg('brand_id_integer_validation', Yii::$app->language)],
            [['status_id'], 'integer', 'message' => getValidationErrorMsg('status_id_integer_validation', Yii::$app->language)],
            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', Yii::$app->language)],
            [['address_id'], 'integer', 'message' => getValidationErrorMsg('address_id_integer_validation', Yii::$app->language)],
            [['dress_type_id'], 'integer', 'message' => getValidationErrorMsg('address_type_id_integer_validation', Yii::$app->language)],
            [['product_tracking_id'], 'integer', 'message' => getValidationErrorMsg('product_tracking_id_integer_validation', Yii::$app->language)],

            [['price'], 'number', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('price_number_validation', Yii::$app->language)],
            [['height'], 'number', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('height_number_validation', Yii::$app->language)],
            [['weight'], 'number', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('weight_number_validation', Yii::$app->language)],
            [['width'], 'number', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('width_number_validation', Yii::$app->language)],
            [['brand_id'], 'required', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('brand_id_required', Yii::$app->language)],

            [['price'], 'number', 'message' => getValidationErrorMsg('price_number_validation', Yii::$app->language)],
            [['height'], 'number', 'message' => getValidationErrorMsg('height_number_validation', Yii::$app->language)],
            [['weight'], 'number', 'message' => getValidationErrorMsg('weight_number_validation', Yii::$app->language)],
            [['width'], 'number', 'message' => getValidationErrorMsg('width_number_validation', Yii::$app->language)],

            [['shipping_country_price'], 'safe'],

            [['option_price'], 'number', 'message' => getValidationErrorMsg('option_price_number_validation', Yii::$app->language)],
            [['refer_price'], 'number', 'message' => getValidationErrorMsg('refer_price_number_validation', Yii::$app->language)],

            [['description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned'], 'string'],
            [['number', 'other_info', 'created_at', 'updated_at', 'is_saved_search_notification_sent'], 'safe'],

            [['name'], 'string', 'max' => 50, 'tooLong' => getValidationErrorMsg('name_max_50_character_length', Yii::$app->language)], //'option_size'

            [['option_conditions'], 'string', 'max' => 100],
            [['option_show_only'], 'string', 'max' => 20],

            [['is_receipt', 'is_admin_favourite'], 'safe'],

            [['images'], 'required', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('product_images_required', Yii::$app->language)],
            [['shipping_country_price'], 'required', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('shipping_country_price_required', Yii::$app->language)],

            [['images'], 'file', 'maxFiles' => 5, 'message' => getValidationErrorMsg('product_max_file_upload_validation', Yii::$app->language)],
            [['receipt'], 'file', 'maxFiles' => 5, 'message' => getValidationErrorMsg('product_receipt_max_file_upload_validation', Yii::$app->language)],

            //[['shipping_country_id', 'shipping_country_price', 'option_color'], 'safe'],
            [['shipping_country_id', 'option_color'], 'safe'],
            [['dress_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DressType::class, 'targetAttribute' => ['dress_type_id' => 'id']],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['category_id' => 'id']],
            [['sub_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['sub_category_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserAddress::class, 'targetAttribute' => ['address_id' => 'id']],

            [
                ['images'], 'required', 'message' => getValidationErrorMsg('product_images_required', Yii::$app->language), 'when' => function ($model) {
            },
                'whenClient' => "function (attribute, value) {
                    if ($('#product-is_product_images_empty').val() == 1) {            
                        return $('#product-images').val() == '';                                    
                    }
                }",
            ],

            [
                ['receipt'], 'required', 'message' => getValidationErrorMsg('product_receipt_required', Yii::$app->language), 'when' => function ($model) {
                return $model->is_receipt == '1';
            },
                'whenClient' => "function (attribute, value) {
                    if ($('#product-is_receipt').val() == 1 && $('#product-is_product_receipt_images_empty').val() ==1) {            
                        return $('#product-receipt').val() == '';                                    
                    }
                }",
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'name' => 'Name',
            'number' => 'Number',
            'category_id' => 'Category',
            'sub_category_id' => 'Sub Category',
            'price' => 'Price',
            'product_tracking_id' => 'Product Tracking',
            'option_size' => 'Option Size',
            'option_price' => 'Option Price',
            'refer_price' => 'Refer Price',
            'option_conditions' => 'Option Conditions',
            'option_show_only' => 'Option Show Only',
            // 'option_color' => 'Option Color',
            'description' => 'Description',
            'available_quantity' => 'Available Quantity',
            'is_top_selling' => 'Is Top Selling',
            'is_top_trending' => 'Is Top Tranding',
            'brand_id' => 'Brand',
            'gender' => 'Gender',
            'is_cleaned' => 'Is Cleaned',
            'height' => 'Height',
            'weight' => 'Weight',
            'width' => 'Width',
            'status_id' => 'Status',
            'address_id' => 'Address',
            'option_color' => 'Color',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'dresstype0' => 'dresstype0',
            'productImages0' => 'productImages0',
            'category0' => 'category0',
            'brand0' => 'brand0',
            'color' => 'color',
            'user0' => 'user0',
            'subCategory0' => 'subCategory0',
            'status' => 'status',
            'address' => 'address',
            'favouriteProduct' => 'favouriteProduct',
            'seller' => 'seller',
            'rating' => 'rating',
            'productReceipt0' => 'productReceipt0',
            'shippingCountry0' => 'shippingCountry0',
            'productTracking' => 'productTracking',
            'productTrackingChild' => 'productTrackingChild',
            'productSizes0' => 'productSizes0',
            'referPrice' => 'referPrice'
        ];
    }

    /**
     * Gets query for [[FavouriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductRatings()
    {
        return $this->hasMany(ProductRating::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::class, ['id' => 'brand_id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDressType()
    {
        return $this->hasOne(DressType::class, ['id' => 'dress_type_id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getColor0()
    {
        return $this->hasOne(Color::class, ['id' => 'option_color']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Address]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(UserAddress::class, ['id' => 'address_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ProductStatus::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[SubCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'sub_category_id']);
    }

    /**
     * Gets query for [[ProductReceipts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductReceipt()
    {
        return $this->hasMany(ProductReceipt::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductShippingCost]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShippingCost()
    {
        return $this->hasMany(ShippingPrice::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[FavouriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductSizes()
    {
        return $this->hasMany(ProductSizes::class, ['product_id' => 'id']);
    }

    /**
     * Uses for admin panel view pages
     *
     * @return string
     */
    public function getProductSizeString()
    {
        $modelsQry = ProductSizes::find();
        $modelsQry->leftJoin('sizes', 'sizes.id=product_sizes.size_id');
        $modelsQry->where(['product_id' => $this->id]);
        $preResult = $modelsQry->select('sizes.size')->asArray()->all();

        $result = "";
        if (!empty($preResult)) {
            $preResult = array_column($preResult, 'size');
            $result = implode(", ", $preResult);
        }
        return $result;
    }

    ///////////////////////For api use only /////////////////////////////////////////////

    /**
     * Gets query for [[ProductImages]] with path for api.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages0()
    {

        $productImages = ProductImage::find()->where(['product_id' => $this->id])->all();
        if (!empty($productImages)) {
            foreach ($productImages as $key => $value) {
                if ($value instanceof ProductImage) {
                    $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    if (!empty($value->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $value->name)) {
                        $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@productImageAbsolutePath') . '/' . $value->name;
                    }
                    $value->name = $product_images;
                }
            }
        }
        return $productImages;
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory0()
    {
        $productCategory = ProductCategory::find()->where(['id' => $this->category_id])->one();
        if ($productCategory instanceof ProductCategory) {
            $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productCategory->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $productCategory->image)) {
                $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $productCategory->image;
            }
            $productCategory->image = $categoryImage;
        }

        return $productCategory;
    }

    /**
     * Gets query for [[SubCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory0()
    {

        $productSubCategory = ProductCategory::find()->where(['id' => $this->sub_category_id])->one();
        if ($productSubCategory instanceof ProductCategory) {
            $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productSubCategory->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $productSubCategory->image)) {
                $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $productSubCategory->image;
            }
            $productSubCategory->image = $subCategoryImage;
        }
        return $productSubCategory;
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand0()
    {

        $brand = Brand::find()->where(['id' => $this->brand_id])->one();
        if ($brand instanceof Brand) {
            $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($brand->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $brand->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageAbsolutePath') . '/' . $brand->image;
            }
            $brand->image = $brandImage;

            $brandName = "";
            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'english') {
                if (!empty($brand->name)) {
                    $brandName = $brand->name;
                } elseif (empty($brand->name) && !empty($brand->german_name)) {
                    $brandName = $brand->german_name;
                }
            }

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'german') {
                if (!empty($brand->german_name)) {
                    $brandName = $brand->german_name;
                } elseif (empty($brand->german_name) && !empty($brand->name)) {
                    $brandName = $brand->name;
                }
            }
            $brand->name = $brandName;
        }
        return $brand;
    }

    /**
     * Gets query for [[Colors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getColor()
    {
        $colors = explode(",", $this->option_color);
        $color = Color::find()->where(['in', 'id', $colors])->all();

        if (!empty($color)) {
            foreach ($color as $key => $colorRow) {
                if (!empty($colorRow) && $colorRow instanceof Color) {
                    $colorName = "";
                    if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'english') {
                        if (!empty($colorRow->name)) {
                            $colorName = $colorRow->name;
                        } elseif (empty($colorRow->name) && !empty($colorRow->german_name)) {
                            $colorName = $colorRow->german_name;
                        }
                    }

                    if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'german') {
                        if (!empty($colorRow->german_name)) {
                            $colorName = $colorRow->german_name;
                        } elseif (empty($colorRow->german_name) && !empty($colorRow->name)) {
                            $colorName = $colorRow->name;
                        }
                    }
                    $color[$key]['name'] = $colorName;
                }
            }
        }


        return $color;
    }

    /**
     * Gets query for [[DressType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDresstype0()
    {
        $dressType = DressType::find()->where(['id' => $this->dress_type_id])->one();

        if (!empty($dressType) && $dressType instanceof DressType) {
            $dressTypeImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($dressType->image) && file_exists(Yii::getAlias('@dressTypeImageRelativePath') . '/' . $dressType->image)) {
                $dressTypeImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@dressTypeImageAbsolutePath') . '/' . $dressType->image;
            }
            $dressType->image = $dressTypeImage;
        } else {
            $dressType = null;
        }
        return $dressType;
    }

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getUser0()
    {
        $data = User::find()->where(['id' => $this->user_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

    /**
     * Gets query for [[Favourite Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProduct()
    {
        $data = [];
        if (!empty(Yii::$app->user->identity) && Yii::$app->user->identity->id) {
            $modelUser = User::findOne(Yii::$app->user->identity->id);
            $data = FavouriteProduct::find()->joinWith('product AS product')->joinWith('product.user AS productUser')->where(['favourite_products.product_id' => $this->id, 'favourite_products.user_id' => Yii::$app->user->identity->id])->andWhere(['NOT IN', 'productUser.id', $modelUser->blockUsersId])->andWhere(['NOT IN', 'productUser.id', $modelUser->abuseUsersId])->one();
        }

        if ($data == '' || $data == null) {
            $data = [];
        } else {
            $data = array($data);
        }

        return $data;
    }

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getSeller()
    {
        $data = User::find()->where(['id' => $this->user_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        $shopDetail['shopDetail'] = (!empty($data->shopDetails)) ? $data->shopDetails : null;
        $data = array_merge($data->toArray(), $shopDetail);
        return $data;
    }

    /**
     * @return object
     */
    public function getRating()
    {
        $modelRate['total_rated_count'] = (int)number_format(ProductRating::find()->where(['product_id' => $this->id])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count(), 1);
        $modelRate['over_all_rate'] = number_format(ProductRating::find()->where(['product_id' => $this->id])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->average('rating'), 2);
        $modelRate['one_star_rate'] = (int)ProductRating::find()->where(['product_id' => $this->id, 'rating' => ProductRating::ONE_STAR_RATE])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count();
        $modelRate['two_star_rate'] = (int)ProductRating::find()->where(['product_id' => $this->id, 'rating' => ProductRating::TWO_STAR_RATE])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count();
        $modelRate['three_star_rate'] = (int)ProductRating::find()->where(['product_id' => $this->id, 'rating' => ProductRating::THREE_STAR_RATE])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count();
        $modelRate['four_star_rate'] = (int)ProductRating::find()->where(['product_id' => $this->id, 'rating' => ProductRating::FOUR_STAR_RATE])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count();
        $modelRate['five_star_rate'] = (int)ProductRating::find()->where(['product_id' => $this->id, 'rating' => ProductRating::FIVE_STAR_RATE])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count();

        return (object)$modelRate;
    }

    /**
     * Gets query for [[ProductReceipts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductReceipt0()
    {
        $productReceipts = ProductReceipt::find()->where(['product_id' => $this->id])->all();
        if (!empty($productReceipts)) {
            foreach ($productReceipts as $key => $value) {
                if ($value instanceof ProductReceipt) {
                    $product_Receipt_image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    if (!empty($value->file) && file_exists(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $value->file)) {
                        $product_Receipt_image = Yii::$app->request->getHostInfo() . Yii::getAlias('@productReceiptImageAbsolutePath') . '/' . $value->file;
                    }
                    $value->file = $product_Receipt_image;
                }
            }
        }
        return $productReceipts;
    }

    /**
     * Gets query for [[ShippingAddress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShippingCountry0()
    {
        $country = ShippingPrice::find()->where(['product_id' => $this->id])->all();
        $data = [];
        if (!empty($country)) {
            foreach ($country as $key => $countryRow) {
                $result['shippingCost'] = $countryRow->shippingCost->toArray();
                $data[] = array_merge($countryRow->toArray(), $result);
            }
            $country = $data;
        }
        return $country;
    }

    /**
     * Gets query for [[ProductTrackings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductTrackings()
    {
        return $this->hasMany(ProductTracking::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductTracking]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductTracking()
    {

        $modelProductTracking = [];
        if (!empty($this->product_tracking_id)) {
            $modelProductTracking = ProductTracking::find()->where(['id' => $this->product_tracking_id])->orWhere(['parent_id' => $this->product_tracking_id])->orderBy(['id' => SORT_ASC])->all();
        }
        return $modelProductTracking;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getProductSizes0()
    {
        $ProductSizes = ProductSizes::find()->where(['product_id' => $this->id])->all();
        $data = [];
        if (!empty($ProductSizes)) {
            foreach ($ProductSizes as $key => $ProductSizesRow) {
                $result['size'] = $ProductSizesRow->size->toArray();
                $data[] = array_merge($ProductSizesRow->toArray(), $result);
            }
            $ProductSizes = $data;
        }
        return $ProductSizes;
    }

    /**
     * @return mixed
     */
    public function getMakeOffers()
    {
        $models = MakeOffer::find()->where(['product_id' => $this->id])->andWhere(['sender_id' => Yii::$app->user->identity->id])->all();
        return $models;
    }

    /**
     * @return float|int|null
     */
    public function getReferPrice()
    {

        $dataResult['ref_price'] = $this->price;

        if ($this->type == Product::PRODUCT_TYPE_USED && Yii::$app->user->identity->id != $this->user_id) {
            $isOfferAcceptedCount = "";
            $offers = $this->makeOffers;

            if (!empty($offers)) {
                foreach ($offers as $key => $offersRow) {
                    if (!empty($offersRow) && $offersRow instanceof MakeOffer && $offersRow->sender_id == Yii::$app->user->identity->id && $offersRow->status == MakeOffer::STATUS_ACCEPT) {
                        $isOfferAcceptedCount = $key;
                    }
                }
            }
            //p($isOfferAcceptedCount);
            if (!empty($isOfferAcceptedCount) || $isOfferAcceptedCount != "") {
                $dataResult['ref_price'] = $offers[$isOfferAcceptedCount]['offer_amount'];
            }
        }

        $this->refer_price = $dataResult['ref_price'];

        return $this->refer_price;
    }

}