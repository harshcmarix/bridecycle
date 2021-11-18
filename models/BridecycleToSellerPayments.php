<?php

namespace app\models;

use app\models\Order;
use app\models\Product;
use app\models\OrderItem;
use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "bridecycle_to_seller_payments".
 *
 * @property int $id
 * @property int|null $order_id
 * @property int $order_item_id
 * @property int|null $product_id
 * @property int $seller_id
 * @property float $amount
 * @property float|null $product_price
 * @property float|null $tax
 * @property int $status
 * @property string|null $note_content
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 * @property OrderItem $orderItem
 * @property User $seller
 * @property Product $product
 */
class BridecycleToSellerPayments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bridecycle_to_seller_payments';
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

    const STATUS_PENDING = '0';
    const STATUS_COMPLETE = '1';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'order_item_id', 'product_id', 'seller_id', 'status'], 'integer'],
            [['order_id', 'order_item_id', 'seller_id', 'amount', 'status'], 'required'],
            [['amount', 'product_price', 'tax'], 'number'],
            [['note_content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['order_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderItem::className(), 'targetAttribute' => ['order_item_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['seller_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'order_item_id' => 'Order Item ID',
            'product_id' => 'Product',
            'seller_id' => 'Seller',
            'amount' => 'Amount',
            'product_price' => 'Product Price',
            'tax' => 'Tax',
            'status' => 'Status',
            'note_content' => 'Bridecycle Note',
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
            'order' => 'order',
            'orderItem' => 'orderItem',
            'seller0' => 'seller0',
            'product' => 'product'
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * Gets query for [[OrderItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItem()
    {
        return $this->hasOne(OrderItem::className(), ['id' => 'order_item_id']);
    }

    /**
     * Gets query for [[Seller]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeller()
    {
        return $this->hasOne(User::className(), ['id' => 'seller_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
    ///////////////////////For api use only /////////////////////////////////////////////

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getSeller0()
    {
        $data = User::find()->where(['id' => $this->seller_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }
}
