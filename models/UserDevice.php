<?php

namespace app\models;

/**
 * This is the model class for table "user_device".
 *
 * @property int $id
 * @property int $user_id
 * @property string $notification_token
 * @property string $device_platform
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class UserDevice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_device';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'notification_token'], 'required'],
            [['user_id'], 'integer'],
            [['device_platform'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['notification_token'], 'string', 'max' => 500],
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
            'notification_token' => 'Notification Token',
            'device_platform' => 'Device Platform',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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

}
