<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_statuses".
 *
 * @property int $id
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Product[] $products
 */
class ProductStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_statuses';
    }

    const STATUS_PENDING_APPROVAL = '1';
    const STATUS_APPROVED = '2';
    const STATUS_IN_STOCK = '3';
    const STATUS_SOLD = '4';
    const STATUS_ARCHIVED = '5';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
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
        return $this->hasMany(Product::class, ['status_id' => 'id']);
    }

}
