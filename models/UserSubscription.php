<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use app\modules\api\v2\models\User;

/**
 * This is the model class for table "user_subscriptions".
 *
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property string|null $card_holder_name
 * @property string|null $card_type
 * @property string|null $payment_response
 * @property string|null $payment_status
 * @property string|null $transaction_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Subscription $subscription
 * @property User $user
 */
class UserSubscription extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_subscriptions';
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

    const  CARD_TYPE_VISA_NUMBER = '4';
    const  CARD_TYPE_MASTER_NUMBER_ONE = '5';
    const  CARD_TYPE_MASTER_NUMBER_TWO = '2';
    const  CARD_TYPE_AMEX_NUMBER = '3';
    const  CARD_TYPE_DISCOVER_NUMBER = '6';
    //const  CARD_TYPE_MAESTRO_NUMBER = 'maestro';

    const  CARD_TYPE_VISA = 'visa';
    const  CARD_TYPE_MASTER = 'mastercard';
    const  CARD_TYPE_AMEX = 'amex';
    const  CARD_TYPE_DISCOVER = 'discover';
    //const  CARD_TYPE_MAESTRO = 'maestro';


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'subscription_id', 'card_holder_name', 'card_number', 'expiry_month_year', 'cvv'], 'required'],
            [['user_id', 'subscription_id'], 'integer'],
            [['card_number', 'cvv'], 'integer'],
            [['card_holder_name', 'payment_response', 'payment_status', 'transaction_id', 'card_type', 'expiry_month_year', 'created_at', 'updated_at'], 'safe'],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscription::className(), 'targetAttribute' => ['subscription_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'subscription_id' => 'Subscription ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Subscription]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['id' => 'subscription_id']);
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
