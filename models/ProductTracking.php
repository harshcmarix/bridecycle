<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_tracking".
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int|null $user_id
 * @property int|null $product_id
 * @property int|null $order_id
 * @property string|null $location
 * @property float|null $price
 * @property string|null $resale_date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 * @property Product $product
 * @property User $user
 * @property Product[] $products
 */
class ProductTracking extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_tracking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'user_id', 'product_id', 'order_id'], 'integer'],
            [['price'], 'number'],
            [['resale_date', 'created_at', 'updated_at'], 'safe'],
            [['location'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'user_id' => 'User ID',
            'product_id' => 'Product ID',
            'order_id' => 'Order ID',
            'location' => 'Location',
            'price' => 'Price',
            'resale_date' => 'Resale Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['product_tracking_id' => 'id']);
    }

    /**
     * Gets query for [[ProductTrackingChild]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductTrackingChild()
    {
        return $this->hasMany(ProductTracking::class, ['id' => 'parent_id']);
    }
}