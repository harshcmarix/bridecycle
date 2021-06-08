<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $notification_receiver_id
 * @property int $ref_id
 * @property string $notification_text
 * @property string $action
 * @property string $ref_type
 * @property string|null $created_at
 *
 * @property User $owner
 * @property User $notificationReceiver
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
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
            [['owner_id', 'notification_receiver_id', 'ref_id'], 'integer'],
            [['ref_id', 'notification_text', 'action', 'ref_type'], 'required'],
            [['created_at'], 'safe'],
            [['notification_text', 'action', 'ref_type'], 'string', 'max' => 255],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['owner_id' => 'id']],
            [['notification_receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['notification_receiver_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'notification_receiver_id' => 'Notification Receiver ID',
            'ref_id' => 'Ref ID',
            'notification_text' => 'Notification Text',
            'action' => 'Action',
            'ref_type' => 'Ref Type',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'owner0' => 'owner0',
            'notificationReceiver0' => 'notificationReceiver0',
        ];
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::className(), ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[NotificationReceiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationReceiver()
    {
        return $this->hasOne(User::className(), ['id' => 'notification_receiver_id']);
    }

    ///////////////////////////// Use for APIs //////////////////////////


    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner0()
    {
        return $this->hasOne(User::className(), ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[NotificationReceiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationReceiver0()
    {
        return $this->hasOne(User::className(), ['id' => 'notification_receiver_id']);
    }
}
