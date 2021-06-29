<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chat_history".
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $from_user_id
 * @property int|null $to_user_id
 * @property string|null $message
 * @property string|null $message_type
 * @property string $chat_type
 * @property int $is_read '0'=>'no','1'=>'yes'
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Products $product
 * @property Users $fromUser
 * @property Users $toUser
 */
class ChatHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'from_user_id', 'to_user_id', 'is_read'], 'integer'],
            [['message', 'message_type', 'chat_type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['from_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['from_user_id' => 'id']],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['to_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'from_user_id' => 'From User ID',
            'to_user_id' => 'To User ID',
            'message' => 'Message',
            'message_type' => 'Message Type',
            'chat_type' => 'Chat Type',
            'is_read' => 'Is Read',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Products::className(), ['id' => 'product_id']);
    }

    /**
     * Gets query for [[FromUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'from_user_id']);
    }

    /**
     * Gets query for [[ToUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'to_user_id']);
    }
}
