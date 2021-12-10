<?php

namespace app\models;

use Yii;
use app\modules\api\v2\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_purchased_subscriptions".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $transaction_id
 * @property string|null $subscription_id
 * @property float $amount
 * @property string|null $date_time
 * @property string|null $status
 * @property string|null $subscription_type
 * @property string|null $device_platform
 * @property string|null $subscription_response
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class UserPurchasedSubscriptions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_purchased_subscriptions';
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

    const DEVICE_PLATFORM_ANDROID = "android";
    const DEVICE_PLATFORM_IOS= "ios";

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'transaction_id', 'subscription_id', 'amount', 'date_time', 'status', 'subscription_type', 'device_platform', 'subscription_response'], 'required', 'on' => 'create_api'],
            [['user_id'], 'required', 'on' => 'cancel_fail_api'],
            [['user_id'], 'integer'],
            [['transaction_id', 'subscription_id'], 'string'],
            [['amount'], 'number'],
            [['date_time', 'device_platform', 'subscription_response', 'created_at', 'updated_at'], 'safe'],
            [['status', 'subscription_type'], 'string', 'max' => 250],
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
            'transaction_id' => 'Transaction ID',
            'subscription_id' => 'Subscription ID',
            'amount' => 'Amount',
            'date_time' => 'Subscription Date-Time',
            'status' => 'Status',
            'subscription_type' => 'Subscription Type',
            'device_platform' => 'Device Platform',
            'subscription_response' => 'Subscription Response',
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
            'user0' => 'user0',
        ];
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

    ///////////////////////For api use only /////////////////////////////////////////////

    public function getUserLastSubscription()
    {
        return self::find()->where(['user_id' => $this->user_id])->orderBy(['id' => SORT_DESC])->one();
    }

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
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
}