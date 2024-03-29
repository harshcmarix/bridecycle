<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "product_images".
 *
 * @property int $id
 * @property int $product_id
 * @property string|null $name
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 */
class ProductImage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_images';
    }

    public $images;

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
            [['product_id'], 'required', 'message' => getValidationErrorMsg('product_id_required', \Yii::$app->language)],

            [['product_id'], 'required', 'on' => 'update_api', 'message' => getValidationErrorMsg('product_id_required', \Yii::$app->language)],
            [['images'], 'required', 'on' => 'update_api', 'message' => getValidationErrorMsg('product_images_required', \Yii::$app->language)],

            [['product_id'], 'integer', 'message' => getValidationErrorMsg('product_id_integer_validation', \Yii::$app->language)],

            [['created_at', 'updated_at'], 'safe'],
            [['name', 'images'], 'file', 'maxFiles' => 5, 'message' => 'You can upload a maximum of 5 product images only.'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'name' => 'name',
            'images' => 'images',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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

}
