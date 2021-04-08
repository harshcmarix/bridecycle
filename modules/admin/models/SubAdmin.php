<?php

namespace app\modules\admin\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "SubAdmin".
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
 * @property FavouriteProducts[] $favouriteProducts
 * @property Orders[] $orders
 * @property ProductRatings[] $productRatings
 * @property UserAddresses[] $userAddresses
 * @property UserSocialIdentities[] $userSocialIdentities
 * @property UserSubscriptions[] $userSubscriptions
 */
class SubAdmin extends \yii\db\ActiveRecord
{
    const SUB_ADMIN_CREATE = 'create';
    const SUB_ADMIN_UPDATE = 'update';
    const USER_TYPE = [
        '1' => 'Admin',
        '2' => 'Sub-admin',
        '3' => 'User',
    ];

    public $password;
    /**
     * @var string
     */
    public $confirm_password;
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email'], 'required'],
            [['access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['mobile', 'shop_phone_number'], 'integer'],
            [['weight', 'height'], 'number'],
            [['personal_information', 'user_type', 'is_shop_owner'], 'string'],
            [['password', 'confirm_password'], 'required','on'=>self::SUB_ADMIN_CREATE],
            [['profile_picture', 'password', 'temporary_password', 'access_token', 'password_reset_token'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 60],
            [['shop_name', 'shop_email'], 'string', 'max' => 100],
            [['email'], 'unique'],
             ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Confirm Password don't match"],
        ];
    }

    /**
     * {@inheritdoc}
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
     * Gets query for [[FavouriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProducts::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ProductRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductRatings()
    {
        return $this->hasMany(ProductRatings::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddresses::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserSocialIdentities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSocialIdentities()
    {
        return $this->hasMany(UserSocialIdentities::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserSubscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscriptions()
    {
        return $this->hasMany(UserSubscriptions::className(), ['user_id' => 'id']);
    }
}