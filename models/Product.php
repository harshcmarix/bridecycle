<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

// use app\modules\admin\models\User;
use app\modules\api\v1\models\User;

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
 * @property string|null $option_conditions
 * @property string|null $option_show_only
 * @property string|null $description
 * @property int|null $available_quantity
 * @property string $is_top_selling 1 => top selling product
 * @property string $is_top_trending 1 => top trending product
 * @property int $brand_id
 * @property string|null $gender 1 => female
 * @property string $is_cleaned 1 => cleaned product
 * @property int|null $height
 * @property int|null $weight
 * @property int|null $width
 * @property string|null $receipt
 * @property int $status_id
 * @property int $address_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FavouriteProducts[] $favouriteProducts
 * @property OrderItems[] $orderItems
 * @property ProductRatings[] $productRatings
 * @property ProductImages[] $productImages
 * @property Brand $brand
 * @property Color $color
 * @property Category $category
 * @property SubCategory $subCategory
 * @property UserAddress $address
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

    const IS_TOP_SELLING_YES = '1';
    const IS_TOP_SELLING_NO = '0';

    const IS_TOP_TRENDING_YES = '1';
    const IS_TOP_TRENDING_NO = '0';

    const OPTION_IS_SHOW_ONLY_YES = '1';
    const OPTION_IS_SHOW_ONLY_NO = '0';

    const IS_CLEANED_YES = '1';
    const IS_CLEANED_NO = '0';

    const GENDER_FOR_FEMALE = '1';
    const GENDER_FOR_MALE = '0';
    const GENDER_FOR_ALL = '3';

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


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'number', 'category_id', 'price', 'available_quantity', 'gender', 'is_cleaned', 'status_id'], 'required'], //'is_top_selling', 'is_top_trending',
            [['category_id', 'sub_category_id', 'price', 'available_quantity', 'brand_id', 'height', 'weight', 'width', 'status_id', 'user_id', 'address_id'], 'integer'],
            [['option_price'], 'number'],
            [['description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned'], 'string'],
            [['number', 'other_info', 'created_at', 'updated_at'], 'safe'],
            [['name', 'option_size'], 'string', 'max' => 50],
            [['option_conditions'], 'string', 'max' => 100],
            [['option_show_only'], 'string', 'max' => 20],
            [['images'], 'required', 'on' => self::SCENARIO_CREATE],
            [['images'], 'file', 'maxFiles' => 5, 'extensions' => 'png, jpg'],
            [['receipt', 'option_color'], 'string', 'max' => 255],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['sub_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::className(), 'targetAttribute' => ['sub_category_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductStatus::className(), 'targetAttribute' => ['status_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserAddress::className(), 'targetAttribute' => ['address_id' => 'id']],
            [['images'], 'required', 'when' => function ($model) {
                //return $model->is_image_empty == '1';
            },
                'whenClient' => "function (attribute, value) {
                    if ($('#product-is_product_images_empty').val() == 1) {            
                                    return $('#product-images').val() == '';                                    
                                    }
                                }",],
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
            'option_size' => 'Option Size',
            'option_price' => 'Option Price',
            'option_conditions' => 'Option Conditions',
            'option_show_only' => 'Option Show Only',
            'option_color' => 'Option Color',
            'description' => 'Description',
            'available_quantity' => 'Available Quantity',
            'is_top_selling' => 'Is Top Selling',
            'is_top_trending' => 'Is Top Trending',
            'brand_id' => 'Brand',
            'gender' => 'Gender',
            'is_cleaned' => 'Is Cleaned',
            'height' => 'Height',
            'weight' => 'Weight',
            'width' => 'Width',
            'receipt' => 'Receipt',
            'status_id' => 'Status',
            'address_id' => 'Address',
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
            'productImages0' => 'productImages0',
            'category0' => 'category0',
            'brand0' => 'brand0',
            'color' => 'color',
            'user0' => 'user0',
            'subCategory0' => 'subCategory0',
            'status' => 'status',
            'address' => 'address',
            'favouriteProduct' => 'favouriteProduct',
        ];
    }

    /**
     * Gets query for [[FavouriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProduct::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductRatings()
    {
        return $this->hasMany(ProductRating::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getColor()
    {
        return $this->hasOne(Color::className(), ['id' => 'option_color']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Address]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(UserAddress::className(), ['id' => 'address_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ProductStatus::className(), ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProductCategory::className(), ['id' => 'category_id']);
    }

    /**
     * Gets query for [[SubCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory()
    {
        return $this->hasOne(ProductCategory::className(), ['id' => 'sub_category_id']);
    }

    ///////////////////////For api use only /////////////////////////////////////////////

    /**
     * Gets query for [[ProductImages]] with path for api.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages0()
    {
        // return $this->hasMany(ProductImage::className(), ['product_id' => 'id']);
        $productImages = ProductImage::find()->where(['product_id' => $this->id])->all();
        if (!empty($productImages)) {
            foreach ($productImages as $key => $value) {
                if ($value instanceof ProductImage) {
                    $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    if (!empty($value->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $value->name)) {
                        $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $value->name;
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
        // return $this->hasOne(ProductCategory::className(), ['id' => 'category_id']);
        $productCategory = ProductCategory::find()->where(['id' => $this->category_id])->one();
        if ($productCategory instanceof ProductCategory) {
            $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productCategory->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $productCategory->image)) {
                $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $productCategory->image;
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
        // return $this->hasOne(ProductCategory::className(), ['id' => 'sub_category_id']);
        $productSubCategory = ProductCategory::find()->where(['id' => $this->sub_category_id])->one();
        if ($productSubCategory instanceof ProductCategory) {
            $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productSubCategory->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $productSubCategory->image)) {
                $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $productSubCategory->image;
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
        // return $this->hasOne(Brand::className(), ['id' => 'brand_id']);
        $brand = Brand::find()->where(['id' => $this->brand_id])->one();
        if ($brand instanceof Brand) {
            $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($brand->image) && file_exists(Yii::getAlias('@brandImageThumbRelativePath') . '/' . $brand->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $brand->image;
            }
            $brand->image = $brandImage;
        }
        return $brand;
    }

    public function getUser0()
    {
        //return $this->hasOne(User::className(), ['id' => 'user_id']);
        $data = User::find()->where(['id' => $this->user_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $data->profile_picture;
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
        $data = FavouriteProduct::find()->where(['product_id' => $this->id, 'user_id' => Yii::$app->user->identity->id])->one();
        return $data;
    }
}
