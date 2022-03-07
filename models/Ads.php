<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "ads".
 *
 * @property int $id
 * @property string $title
 * @property string $image
 * @property string $url
 * @property int $category_id
 * @property int $sub_category_id
 * @property int $product_id
 * @property int $brand_id
 * @property int $status '1'=>'inactive','2'=>'active'
 * @property string $created_at
 * @property string|null $updated_at
 */
class Ads extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ads';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d h:i:s'),
            ],
        ];
    }

    /**
     * used for create
     */
    const SCENARIO_CREATE = 'create';

    /**
     * used for image validation
     */
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    public $is_ads_image_empty;

    /**
     * Constants
     */
    const STATUS_INACTIVE = 1;
    const STATUS_ACTIVE = 2;

    const ARR_ADS_STATUS = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active'
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['category_id', 'sub_category_id', 'product_id', 'brand_id'], 'integer'],
            [['url'], 'url'],
            [['status'], 'integer'],
            [['image'], 'file', 'extensions' => 'png, jpg, jpeg'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['image',], 'required', 'on' => self::SCENARIO_CREATE],

            [['image'], 'required', 'when' => function ($model) {
                return $model->scenario == self::SCENARIO_CREATE;
            }, 'whenClient' => "function (attribute, value) {
                    if ($('#ads-is_ads_image_empty').val() == 1) {   
                        
                        return $('#ads-image').val() == '';                                    
                    }
                }",],

            [['url'], 'required', 'message' => '{attribute} cannot be blank or (Product or Brand cannot be blank).', 'when' => function ($model) {
                return ($model->product_id == "" && $model->category_id == "");
            }, 'whenClient' => "function (attribute, value) {
                        if ($('#ads-product_id').val() == '' && $('#ads-brand_id').val() == '') {
                            return $('#ads-url').val() == '';   
                        }
                    }",],

            [['category_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['category_id' => 'id']],
            [['sub_category_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['sub_category_id' => 'id']],
            [['product_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['brand_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Brand::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['image'], 'file', 'extensions' => 'jpg, png'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'image' => 'Image',
            'url' => 'Url',
            'status' => 'Status',
            'category_id' => 'Category',
            'sub_category_id' => 'Sub Category',
            'product_id' => 'Product',
            'brand_id' => 'Brand',
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
            'product0' => 'product0',
            'category0' => 'category0',
            'brand0' => 'brand0',
            'subCategory0' => 'subCategory0',
        ];
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
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
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

    ///////////////////////// For API uses /////////////////////////////////////

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory0()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[SubCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory0()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'sub_category_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct0()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand0()
    {
        //return $this->hasOne(Brand::class, ['id' => 'brand_id']);
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

}
