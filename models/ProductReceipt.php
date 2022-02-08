<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "product_receipt".
 *
 * @property int $id
 * @property int $product_id
 * @property string $file
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product $product
 */
class ProductReceipt extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_receipt';
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
            [['product_id'], 'required', 'message' => getValidationErrorMsg('product_id_required', \Yii::$app->language)],
            [['file'], 'required', 'message' => getValidationErrorMsg('product_receipt_required', \Yii::$app->language)],

            [['product_id'], 'integer', 'message' => getValidationErrorMsg('product_id_integer_validation', \Yii::$app->language)],
            [['created_at', 'updated_at'], 'safe'],
            //[['file'], 'file', 'maxFiles' => 5, 'extensions' => 'jpg, png'],
            [['file'], 'file', 'maxFiles' => 5, 'message' => getValidationErrorMsg('product_receipt_max_file_upload_validation', \Yii::$app->language)],
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
            'file' => 'File',
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
