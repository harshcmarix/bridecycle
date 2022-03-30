<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "order_payment_refund".
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $payment_refund_id
 * @property string $amount
 * @property string|null $refund_status
 * @property string|null $refund_response
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 */
class OrderPaymentRefund extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_payment_refund';
    }

    /**
     * @return array
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
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
            [['refund_response'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['payment_refund_id', 'refund_status'], 'string', 'max' => 255],
            [['amount'], 'string', 'max' => 25],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
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
            'payment_refund_id' => 'Payment Refund ID',
            'amount' => 'Amount',
            'refund_status' => 'Refund Status',
            'refund_response' => 'Refund Response',
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
}
