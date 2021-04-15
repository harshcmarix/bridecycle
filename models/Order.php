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
 * @property int $total_amount
 * @property string $status 1 => pending, 2 => in progress, 3 => completed, 4 => cancelled
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property OrderItems[] $orderItems
 * @property UserAddresses $userAddress
 * @property Users $user
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

    const STATUS_ORDER_PENDING = '1';
    const STATUS_ORDER_INPROGRESS = '2';
    const STATUS_ORDER_COMPLETED = '3';
    const STATUS_ORDER_CANCELLED = '4';

    public $arrOrderStatus = [
        self::STATUS_ORDER_PENDING => 'Pending',
        self::STATUS_ORDER_INPROGRESS => 'In Progress',
        self::STATUS_ORDER_COMPLETED => 'Completed',
        self::STATUS_ORDER_CANCELLED => 'Cancelled',
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_address_id', 'total_amount'], 'required'],
            [['user_id', 'user_address_id', 'total_amount'], 'integer'],
            [['status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserAddress::className(), 'targetAttribute' => ['user_address_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'total_amount' => 'Total Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    /**
     * Gets query for [[UserAddress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddress()
    {
        return $this->hasOne(UserAddress::className(), ['id' => 'user_address_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
