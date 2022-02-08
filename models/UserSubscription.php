<?php

namespace app\models;

use app\modules\api\v2\models\User;
use yii\behaviors\TimestampBehavior;

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
            [['user_id'], 'required', 'message' => getValidationErrorMsg('user_id_required', \Yii::$app->language)],
            [['subscription_id'], 'required', 'message' => getValidationErrorMsg('subscription_id_required', \Yii::$app->language)],
            [['card_holder_name'], 'required', 'message' => getValidationErrorMsg('card_holder_name_required', \Yii::$app->language)],
            [['card_number'], 'required', 'message' => getValidationErrorMsg('card_number_required', \Yii::$app->language)],
            [['expiry_month_year'], 'required', 'message' => getValidationErrorMsg('expiry_month_year_required', \Yii::$app->language)],
            [['cvv'], 'required', 'message' => getValidationErrorMsg('cvv_required', \Yii::$app->language)],

            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', \Yii::$app->language)],
            [['subscription_id'], 'integer', 'message' => getValidationErrorMsg('subscription_id_integer_validation', \Yii::$app->language)],
            [['card_number'], 'integer', 'message' => getValidationErrorMsg('card_number_integer_validation', \Yii::$app->language)],
            [['cvv'], 'integer', 'message' => getValidationErrorMsg('cvv_integer_validation', \Yii::$app->language)],

            [['card_holder_name', 'payment_response', 'payment_status', 'transaction_id', 'card_type', 'expiry_month_year', 'created_at', 'updated_at'], 'safe'],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscription::class, 'targetAttribute' => ['subscription_id' => 'id']],
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
        return $this->hasOne(Subscription::class, ['id' => 'subscription_id']);
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

}
