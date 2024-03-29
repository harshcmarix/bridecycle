<?php

namespace app\models;

use \yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string|null $profile_picture
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $password_hash
 * @property string|null $temporary_password
 * @property string|null $access_token
 * @property string|null $access_token_expired_at
 * @property string|null $password_reset_token
 * @property int|null $mobile
 * @property float|null $weight
 * @property float|null $height
 * @property string|null $personal_information
 * @property string|null $user_type 1 => admin, 2 => sub admin, 3 => normal user
 * @property string $is_shop_owner 1 => shop owner
 * @property string|null $shop_name
 * @property string|null $shop_email
 * @property int|null $shop_phone_number
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FavouriteProduct[] $favouriteProducts
 * @property Order[] $orders
 * @property ProductRating[] $productRatings
 * @property UserAddress[] $userAddresses
 * @property UserSocialIdentities[] $userSocialIdentities
 * @property UserSubscription[] $userSubscriptions
 */
class SubAdmin extends ActiveRecord
{
    /**
     * Used for create constant
     */
    const SCENARIO_CREATE = 'create';

    /**
     * Identify user type constants
     */
    const USER_TYPE_ADMIN = '1';
    const USER_TYPE_SUB_ADMIN = '2';
    const USER_TYPE_NORMAL = '3';

    /**
     * Used for user type dropdown constants
     */
    const USER_TYPE = [
        self::USER_TYPE_ADMIN => 'Admin',
        self::USER_TYPE_SUB_ADMIN => 'Sub-admin',
        self::USER_TYPE_NORMAL => 'User',
    ];

    /**
     * Used in create user
     * @var string
     */
    public $password;

    /**
     * Used in create user
     * @var string
     */
    public $confirm_password;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'users';
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
     * @return array
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email'], 'required'],
            [['email'], 'email'],
            [['access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['weight', 'height'], 'number'],
            [['mobile'],'string', 'max' => 13,'min'=>10],
            [['personal_information', 'user_type', 'is_shop_owner'], 'string'],
            [['password', 'confirm_password'], 'required', 'on' => self::SCENARIO_CREATE],
            [['password', 'confirm_password'], 'string', 'min' => 6,'max'=>255],
            [['profile_picture','temporary_password', 'access_token', 'password_reset_token'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 60],
            [['email'], 'unique', 'message' => 'Email already exist.'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Confirm Password don't match"],
        ];
    }

    /**
     * @return string[]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'profile_picture' => 'Profile Picture',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'password_hash' => 'Password',
            'temporary_password' => 'Temporary Password',
            'access_token' => 'Access Token',
            'access_token_expired_at' => 'Access Token Expired At',
            'password_reset_token' => 'Password Reset Token',
            'mobile' => 'Mobile',
            'weight' => 'Weight',
            'height' => 'Height',
            'personal_information' => 'Personal Information',
            'user_type' => 'User Type',
            'is_shop_owner' => 'Is Shop Owner',
            'shop_name' => 'Shop Name',
            'shop_email' => 'Shop Email',
            'shop_phone_number' => 'Shop Phone Number',
            'password' => 'Password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProduct::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductRatings()
    {
        return $this->hasMany(ProductRating::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddress::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserSocialIdentities()
    {
        return $this->hasMany(UserSocialIdentities::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, ['user_id' => 'id']);
    }

}