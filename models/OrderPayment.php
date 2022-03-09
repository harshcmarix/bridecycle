<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "order_payment".
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $card_holder_name
 * @property string|null $card_type
 * @property string|null $card_number
 * @property string|null $expiry_month_year
 *
 * @property string|null $buyer_bank_account_id
 * @property string|null $seller_bank_account_id
 *
 * @property string|null $payment_id
 * @property string|null $payment_response
 * @property string|null $payment_status
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 */
class OrderPayment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_payment';
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

    public $cvv;

    /**
     * Constants
     */
    const  CARD_TYPE_VISA_NUMBER = '4';
    const  CARD_TYPE_MASTER_NUMBER_ONE = '5';
    const  CARD_TYPE_MASTER_NUMBER_TWO = '2';
    const  CARD_TYPE_AMEX_NUMBER = '3';
    const  CARD_TYPE_DISCOVER_NUMBER = '6';

    const  CARD_TYPE_VISA = 'visa';
    const  CARD_TYPE_MASTER = 'mastercard';
    const  CARD_TYPE_AMEX = 'amex';
    const  CARD_TYPE_DISCOVER = 'discover';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
//            [['card_holder_name'], 'required', 'message' => getValidationErrorMsg('card_holder_name_required', Yii::$app->language)],
//            [['card_number'], 'required', 'message' => getValidationErrorMsg('card_number_required', Yii::$app->language)],
//            [['expiry_month_year'], 'required', 'message' => getValidationErrorMsg('expiry_month_year_required', Yii::$app->language)],
//            [['cvv'], 'required', 'message' => getValidationErrorMsg('cvv_required', Yii::$app->language)],

            [['order_id'], 'integer'],
            [['buyer_bank_account_id', 'seller_bank_account_id', 'payment_response', 'payment_status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
//            [['card_holder_name'], 'string', 'max' => 100],
//            [['card_type'], 'string', 'max' => 20],
//            [['card_number'], 'string', 'max' => 25],
//            [['expiry_month_year'], 'string', 'max' => 7, 'tooLong' => getValidationErrorMsg('expiry_month_year_max_7_character_length', Yii::$app->language)],
            [['payment_id'], 'string', 'max' => 250],
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
            'card_holder_name' => 'Card Holder Name',
            'card_type' => 'Card Type',
            'card_number' => 'Card Number',
            'expiry_month_year' => 'Expiry Month Year',
            'payment_id' => 'Payment ID',
            'payment_response' => 'Payment Response',
            'payment_status' => 'Payment Status',
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
