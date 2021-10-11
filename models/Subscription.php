<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;

/**
 * This is the model class for table "subscriptions".
 *
 * @property int $id
 * @property int $month
 * @property string $name
 * @property float $amount
 * @property string $status 0 => inactive, 1 => active
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property UserSubscriptions[] $userSubscriptions
 */
class Subscription extends ActiveRecord
{
    /**
     * use to identify subscription is active or not
     */
    const ACTIVE = '1';
    const INACTIVE = '0';
    /**
     * used for dropdown
     */
    const SUBSCRIPTION_STATUS_ARRAY = [
        self::ACTIVE =>'Active',
        self::INACTIVE =>'Inactive'
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscriptions';
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
            [['month','name', 'amount','status'], 'required'],
            [['month','amount'], 'number'],
            [['status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['month'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'amount' => 'Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[UserSubscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscriptions()
    {
        return $this->hasMany(UserSubscription::className(), ['subscription_id' => 'id']);
    }
    public function getSubscribedUsersCount()
    {
        return $this->hasMany(UserSubscription::className(), ['subscription_id' => 'id'])->count();
    }
}
