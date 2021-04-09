<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "user_subscriptions".
 *
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Subscriptions $subscription
 * @property Users $user
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'subscription_id'], 'required'],
            [['user_id', 'subscription_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
