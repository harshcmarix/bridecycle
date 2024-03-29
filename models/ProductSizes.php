<?php

namespace app\models;

/**
 * This is the model class for table "product_sizes".
 *
 * @property int $id
 * @property int $product_id
 * @property int $size_id
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 * @property Sizes $size
 */
class ProductSizes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_sizes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id'], 'required', 'message' => getValidationErrorMsg('product_id_required', \Yii::$app->language)],
            [['size_id'], 'required', 'message' => getValidationErrorMsg('size_id_required', \Yii::$app->language)],

            [['product_id'], 'integer', 'message' => getValidationErrorMsg('product_id_integer_validation', \Yii::$app->language)],
            [['size_id'], 'integer', 'message' => getValidationErrorMsg('size_id_integer_validation', \Yii::$app->language)],

            [['created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['size_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sizes::class, 'targetAttribute' => ['size_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product',
            'size_id' => 'Size',
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

    /**
     * Gets query for [[Size]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSize()
    {
        return $this->hasOne(Sizes::class, ['id' => 'size_id']);
    }

}
