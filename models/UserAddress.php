<?php

namespace app\models;

use Yii;
use app\modules\admin\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_addresses".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $type 1 => billing, 2 => shipping, 3 => shop
 * @property string $address
 * @property string $street
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $zip_code
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Orders[] $orders
 * @property Users $user
 */
class UserAddress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_addresses';
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

    const TYPE_BILLING = '1';
    const TYPE_SHIPPING = '2';
    const TYPE_SHOP = '3';


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['type'], 'string'],
            [['address', 'street', 'city', 'state', 'country', 'zip_code'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['address', 'zip_code'], 'string', 'max' => 100],
            [['street', 'city', 'state', 'country'], 'string', 'max' => 50],
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
            'type' => 'Type',
            'address' => 'Address',
            'street' => 'Street',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'zip_code' => 'Zip Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_address_id' => 'id']);
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
