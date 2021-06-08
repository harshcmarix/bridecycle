<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_receipt".
 *
 * @property int $id
 * @property int $product_id
 * @property string $file
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Products $product
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'file', 'updated_at'], 'required'],
            [['product_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['file'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::className(), 'targetAttribute' => ['product_id' => 'id']],
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
        return $this->hasOne(Products::className(), ['id' => 'product_id']);
    }
}
