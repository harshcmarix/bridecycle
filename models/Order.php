<?php

namespace app\models;

use Yii;
use app\modules\admin\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property int $user_id
 * @property int $user_address_id
 * @property string|null $name
 * @property string|null $contact
 * @property string|null $email
 * @property int $total_amount
 * @property string $status 1 => pending, 2 => in progress, 3 => in transit, 4 => delivered, 5 => return, 6 => cancel
 * @property string|null $transit_detail
 * @property int|null $is_payment_refunded 1 => yes, 0 => no
 * @property string|null $unique_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property OrderItem[] $orderItems
 * @property UserAddress $userAddress
 * @property User $user
 * @property OrderPayment $orderPayment
 * @property OrderReturn $orderReturn
 * @property OrderPaymentRefund $orderPaymentRefund
 * @property PaymentTransferDetails $paymentTransferDetail
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
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

    public $is_return_available;
    /**
     * Constants
     */
    const STATUS_ORDER_PENDING = '1';
    const STATUS_ORDER_INPROGRESS = '2';
    const STATUS_ORDER_IN_TRANSIT = '3';
    const STATUS_ORDER_DELIVERED = '4';
    const STATUS_ORDER_RETURN = '5';
    const STATUS_ORDER_CANCEL = '6';
    const STATUS_ORDER_CANCEL_BY_SELLER = '7';

    public $arrOrderStatus = [
        self::STATUS_ORDER_PENDING => 'Pending',
        self::STATUS_ORDER_INPROGRESS => 'In Progress',
        self::STATUS_ORDER_IN_TRANSIT => 'In-transit',
        self::STATUS_ORDER_DELIVERED => 'Delivered',
        self::STATUS_ORDER_RETURN => 'Returned',
        self::STATUS_ORDER_CANCEL => 'Cancelled',
        self::STATUS_ORDER_CANCEL_BY_SELLER => 'Cancelled by seller',

    ];

    public $arrOrderStatuses = [
        self::STATUS_ORDER_PENDING => 'Pending',
        self::STATUS_ORDER_INPROGRESS => 'In Progress',
        self::STATUS_ORDER_IN_TRANSIT => 'In-transit',
        self::STATUS_ORDER_DELIVERED => 'Delivered',
        self::STATUS_ORDER_RETURN => 'Returned',
        self::STATUS_ORDER_CANCEL => 'Cancelled',
        self::STATUS_ORDER_CANCEL_BY_SELLER => 'Cancelled by seller',
    ];

    const IS_PAYMENT_REFUNDED_YES = '1';
    const IS_PAYMENT_REFUNDED_NO = '0';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_address_id', 'total_amount', 'status'], 'required'],
            [['user_id', 'user_address_id', 'total_amount'], 'integer'],
            [['status'], 'string'],
            [['transit_detail'], 'string'],
            //[['unique_id'], 'string'],
            [['unique_id'], 'unique'],
            [['name', 'contact', 'email'], 'string'],
            [['created_at', 'updated_at', 'is_return_available', 'is_payment_refunded'], 'safe'],
            [['user_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserAddress::class, 'targetAttribute' => ['user_address_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Order ID',
            'user_id' => 'User',
            'user_address_id' => 'User Address',
            'name' => 'Name',
            'contact' => 'Contact',
            'email' => 'Email',
            'total_amount' => 'Total Amount',
            'status' => 'Status',
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
            'orderItems' => 'orderItems',
            'userAddress' => 'userAddress',
            'user' => 'user',
            'user0' => 'user0',
            'orderItems0' => 'orderItems0',
            'isReturnAvailable' => 'isReturnAvailable',
            'orderReturn' => 'orderReturn',
            'orderPaymentRefund' => 'orderPaymentRefund',
            'productReturnAllowDays' => 'productReturnAllowDays',
        ];
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[UserAddress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddress()
    {
        return $this->hasOne(UserAddress::class, ['id' => 'user_address_id']);
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
     * Gets query for [[OrderPayment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPayment()
    {
        return $this->hasOne(OrderPayment::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderReturn]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderReturn()
    {
        return $this->hasOne(OrderReturn::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderPaymentRefund]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPaymentRefund()
    {
        return $this->hasOne(OrderPaymentRefund::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderPaymentRefund]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentTransferDetail()
    {
        return $this->hasOne(PaymentTransferDetails::class, ['order_id' => 'id']);
    }

/////////////////////////////////// For API Use //////////////////////////////////////////

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems0()
    {
        $modelOrderItems = OrderItem::find()->where(['order_id' => $this->id])->one();
        if ($modelOrderItems instanceof OrderItem) {

            if (!empty($modelOrderItems->invoice) && file_exists(Yii::getAlias('@orderInvoiceRelativePath') . "/" . $modelOrderItems->invoice)) {
                $modelOrderItems->invoice = Yii::$app->request->getHostInfo() . Yii::getAlias('@orderInvoiceAbsolutePath') . "/" . $modelOrderItems->invoice;
            }
        }
        return $modelOrderItems;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getUser0()
    {
        $data = User::find()->where(['id' => $this->user_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

    /**
     * @return int
     */
    public function getIsReturnAvailable()
    {

        $this->is_return_available = 'no';
        $orderItem = OrderItem::find()->where(['order_id' => $this->id])->one();
        if (!empty($orderItem) && $orderItem instanceof OrderItem) {
            if (!empty($orderItem->product) && $orderItem->product instanceof Product) {
                $modelProduct = $orderItem->product;
                if (!empty($modelProduct) && $modelProduct->is_return_allow == Product::IS_RETURN_ALLOW_YES) {

                    if ($this->status == Order::STATUS_ORDER_DELIVERED) {
                        $modelOrder = Order::find()->where(['id' => $this->id])->one();
                        $productDeliveredDate = strtotime($modelOrder->updated_at);
                        $currentDate = time();

                        // calulating the difference in timestamps
                        $differenceResult = $this->getDays($productDeliveredDate, $currentDate);
                        //p($differenceResult);
                        if ($differenceResult >= 0 && $differenceResult <= Yii::$app->params['allow_return_product_days']) {
                            $this->is_return_available = 'yes';
                        }
                    }
                }
            }
        }
        return $this->is_return_available;
        //return $data;
    }

    /**
     * @return mixed
     */
    public function getProductReturnAllowDays()
    {

        return Yii::$app->params['allow_return_product_days'];
    }

    /**
     * @param $startdate
     * @param $enddate
     * @return float
     */
    public function getDays($startdate, $enddate)
    {
        $datediff = $enddate - $startdate;

        return round($datediff / (60 * 60 * 24));

    }
}
