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
 * @property string $status 1 => pending, 2 => in progress, 3 => completed, 4 => cancelled
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property OrderItem[] $orderItems
 * @property UserAddress $userAddress
 * @property User $user
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

    /**
     * Constants
     */
    const STATUS_ORDER_PENDING = '1';
    const STATUS_ORDER_INPROGRESS = '2';
    const STATUS_ORDER_COMPLETED = '3';
    const STATUS_ORDER_CANCELLED = '4';
    const STATUS_ORDER_SHIPPED = '5';
    const STATUS_ORDER_DELIVERED = '6';

    public $arrOrderStatus = [
        // self::STATUS_ORDER_PENDING => 'Pending',
        self::STATUS_ORDER_INPROGRESS => 'In Progress',
        self::STATUS_ORDER_COMPLETED => 'Completed',
        self::STATUS_ORDER_CANCELLED => 'Cancelled',
        self::STATUS_ORDER_SHIPPED => 'Shipped',
        self::STATUS_ORDER_DELIVERED => 'Delivered',
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_address_id', 'total_amount', 'status'], 'required'],
            [['user_id', 'user_address_id', 'total_amount'], 'integer'],
            [['status'], 'string'],
            [['name', 'contact', 'email'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
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
            'orderItems0' => 'orderItems0',
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

}
