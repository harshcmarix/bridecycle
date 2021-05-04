<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "product_categories".
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property int|null $parent_category_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Product[] $products
 * @property Product[] $products0
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * used for create
     */
    const SCENARIO_CREATE = 'create';
    /**
     * used for image validation 
     */
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    public $is_image_empty;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_categories';
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_category_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique'],
            [['image'], 'file', 'extensions' => 'png,jpg'],
            [['image'], 'required','on'=>self::SCENARIO_CREATE],
            //[['image'], 'string', 'max' => 255],
            [['image'], 'required', 'when' => function ($model) {
                //return $model->is_image_empty == '1';
            },
                'whenClient' => "function (attribute, value) {
                    if ($('#productcategory-is_image_empty').val() == 1) {            
                                    return $('#productcategory-image').val() == '';                                    
                                    }
                                }",],
            [['parent_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::className(), 'targetAttribute' => ['parent_category_id' => 'id']],
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
            'image' => 'Image',
            'parent_category_id' => 'Parent Category',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Products0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts0()
    {
        return $this->hasMany(Product::className(), ['sub_category_id' => 'id']);
    }
    /**
     * Gets query for [[ProductCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    // public function getProductCategories()
    // {
    //     return $this->hasOne(ProductCategory::className(), ['id' => 'parent_category_id']);
    // }
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['parent_category_id' => 'id']);
    }


    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_category_id']);
    }
}
