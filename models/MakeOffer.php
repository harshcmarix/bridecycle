<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "make_offer".
 *
 * @property int $id
 * @property int $product_id
 * @property int $sender_id
 * @property int $receiver_id
 * @property float $offer_amount
 * @property int $status '1'=>'pending','2'=>'accept','3'=>'reject'	
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Products $product
 * @property Users $sender
 * @property Users $receiver
 */
class MakeOffer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'make_offer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'sender_id', 'receiver_id', 'status'], 'required'],
            [['product_id', 'sender_id', 'receiver_id', 'status'], 'integer'],
            [['offer_amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['sender_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['receiver_id' => 'id']],
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
            'sender_id' => 'Sender ID',
            'receiver_id' => 'Receiver ID',
            'offer_amount' => 'Offer Amount',
            'status' => 'Status',
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
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(Users::className(), ['id' => 'sender_id']);
    }

    /**
     * Gets query for [[Receiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(Users::className(), ['id' => 'receiver_id']);
    }
}
