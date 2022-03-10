<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "order_items".
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string|null $product_name
 * @property string|null $category_name
 * @property string|null $subcategory_name
 * @property int|null $seller_id
 * @property int $quantity
 * @property string|null $color
 * @property string|null $price
 * @property string|null $tax
 * @property float|null $shipping_cost
 * @property string|null $order_tracking_id
 * @property string|null $invoice
 * @property int|null $size_id
 * @property string|null $size
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 * @property Product $product
 */
class OrderItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_items';
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
            [['order_id', 'product_id', 'quantity'], 'required'],
            [['order_id', 'product_id', 'quantity', 'size', 'seller_id', 'size_id'], 'integer'],
            [['shipping_cost', 'price', 'tax'], 'number'],
            [['product_name', 'category_name', 'subcategory_name'], 'safe'],
            [['color'], 'string', 'max' => 100],
            [['order_tracking_id'], 'unique'],
            [['invoice', 'created_at', 'updated_at'], 'safe'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['seller_id' => 'id']],
            [['size_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Sizes::class, 'targetAttribute' => ['size_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'product_id' => 'Product ID',
            'quantity' => 'Quantity',
            'order_tracking_id' => 'Order Tracking ID',
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
            'product' => 'product',
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
        //return $this->hasOne(Product::class, ['id' => 'product_id']);
        $model = Product::find()->where(['id' => $this->product_id])->one();
        if (!empty($model) && $model instanceof Product) {
            $model->price = $model->getReferPrice();
        }
        return $model;
    }

    /**
     * @param $price
     * @return float|int
     */
    public function getBrideEarning($price)
    {
        $earnPrice = (($price * Yii::$app->params['bridecycle_product_order_charge_percentage']) / 100);
        return $earnPrice;
    }

}
