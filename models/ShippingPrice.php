<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "shipping_price".
 *
 * @property int $id
 * @property int $product_id
 * @property int $shipping_cost_id
 * @property float $price
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 * @property ShippingCost $shippingCost
 */
class ShippingPrice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shipping_price';
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
            [['product_id', 'shipping_cost_id', 'price'], 'required'],
            [['product_id', 'shipping_cost_id'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['shipping_cost_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShippingCost::class, 'targetAttribute' => ['shipping_cost_id' => 'id']],
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
            'shipping_cost_id' => 'Shipping Cost ID',
            'price' => 'Price',
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
     * Gets query for [[ShippingCost]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShippingCost()
    {
        return $this->hasOne(ShippingCost::class, ['id' => 'shipping_cost_id']);
    }
}
