<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $id
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
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FavouriteProducts[] $favouriteProducts
 * @property OrderItems[] $orderItems
 * @property ProductRatings[] $productRatings
 * @property Brand $brand
 * @property Category $category
 * @property SubCategory $subCategory
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    const IS_TOP_SELLING_YES = '1';
    const IS_TOP_SELLING_NO = '0';

    const IS_TOP_TRENDING_YES = '1';
    const IS_TOP_TRENDING_NO = '0';

    const OPTION_IS_SHOW_ONLY_YES = '1';
    const OPTION_IS_SHOW_ONLY_NO = '0';

    const IS_CLEANED_YES = '1';
    const IS_CLEANED_NO = '0';

    const GENDER_FOR_FEMALE = '1';
    const GENDER_FOR_MALE = '2';
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
        self::GENDER_FOR_MALE => 'Male',
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
            [['name', 'number', 'category_id', 'price', 'available_quantity','gender','is_cleaned'], 'required'],
            [['category_id', 'sub_category_id', 'price', 'available_quantity', 'brand_id', 'height', 'weight', 'width'], 'integer'],
            [['option_price'], 'number'],
            [['description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned'], 'string'],
            [['number', 'created_at', 'updated_at'], 'safe'],
            [['name', 'option_size'], 'string', 'max' => 50],
            [['option_conditions'], 'string', 'max' => 100],
            [['option_show_only'], 'string', 'max' => 20],
            [['receipt'], 'string', 'max' => 255],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['sub_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::className(), 'targetAttribute' => ['sub_category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'number' => 'Number',
            'category_id' => 'Category',
            'sub_category_id' => 'Sub Category',
            'price' => 'Price',
            'option_size' => 'Option Size',
            'option_price' => 'Option Price',
            'option_conditions' => 'Option Conditions',
            'option_show_only' => 'Option Show Only',
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[FavouriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProducts::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductRatings()
    {
        return $this->hasMany(ProductRatings::className(), ['product_id' => 'id']);
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
}
