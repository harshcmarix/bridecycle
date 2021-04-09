<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "subscriptions".
 *
 * @property int $id
 * @property string $name
 * @property float $amount
 * @property string $status 0 => inactive, 1 => active
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property UserSubscriptions[] $userSubscriptions
 */
class Subscription extends \yii\db\ActiveRecord
{
    const SUBSCRIPTION_STATUS_ARRAY = [
        '1'=>'Active',
        '0'=>'Inactive'
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
            [['name', 'amount','status'], 'required'],
            [['amount'], 'number'],
            [['status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
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
}
