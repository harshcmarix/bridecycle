<?php

namespace app\modules\api\v2\models;

use app\models\Order;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

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
 * @property string $is_primary_address
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Order[] $orders
 * @property User $user
 */
class UserAddress extends ActiveRecord
{
    /**
     * address type
     */
    const BILLING = '1';
    const SHIPPING = '2';
    const SHOP = '3';

    /**
     * Constants
     */
    const TYPE_BILLING = '1';
    const TYPE_SHIPPING = '2';
    const TYPE_SHOP = '3';

    const IS_ADDRESS_PRIMARY_YES = "1";
    const IS_ADDRESS_PRIMARY_NO = "0";



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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', Yii::$app->language)],
            [['is_primary_address', 'type'], 'string'],

            [['address'], 'required', 'message' => getValidationErrorMsg('address_required', Yii::$app->language)],
            [['street'], 'required', 'message' => getValidationErrorMsg('street_required', Yii::$app->language)],
            [['city'], 'required', 'message' => getValidationErrorMsg('city_required', Yii::$app->language)],
            [['state'], 'required', 'message' => getValidationErrorMsg('state_required', Yii::$app->language)],
            [['country'], 'required', 'message' => getValidationErrorMsg('country_required', Yii::$app->language)],
            [['zip_code'], 'required', 'message' => getValidationErrorMsg('post_code_required', Yii::$app->language)],

            [['created_at', 'updated_at'], 'safe'],
            [['address', 'zip_code'], 'string', 'max' => 100],

            [['street'], 'string', 'max' => 50, 'tooLong' => getValidationErrorMsg('street_max_50_character_length', Yii::$app->language)],

            [['city', 'state', 'country'], 'string', 'max' => 50],
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
        return $this->hasMany(Order::class, ['user_address_id' => 'id']);
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