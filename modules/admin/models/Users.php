<?php

namespace app\modules\admin\models;

use Yii;

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
 * @property FavouriteProducts[] $favouriteProducts
 * @property Orders[] $orders
 * @property ProductRatings[] $productRatings
 * @property UserAddresses[] $userAddresses
 * @property UserSocialIdentities[] $userSocialIdentities
 * @property UserSubscriptions[] $userSubscriptions
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    const SCENARIO_CREATE_NORMAL_USER = 'create_normal_user';
    const SCENARIO_UPDATE_NORMAL_USER = 'update_normal_user';

    const IS_SHOP_OWNER_YES = '1';
    const IS_SHOP_OWNER_NO = '0';

    public $isShopOwner = [
        //' ' => 'Select',
        self::IS_SHOP_OWNER_YES => 'Yes',
        self::IS_SHOP_OWNER_NO => 'No'
    ];

    /**
     * Users type
     *
     * @var
     */

    const USER_TYPE_ADMIN = '1';
    const USER_TYPE_SUB_ADMIN = '2';
    const USER_TYPE_NORMAL_USER = '3';

    public $confirm_password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'mobile'], 'required'],

            [['first_name', 'last_name', 'email', 'mobile', 'password_hash', 'confirm_password'], 'required', 'on' => [self::SCENARIO_CREATE_NORMAL_USER]],
            [['first_name', 'last_name', 'email', 'mobile'], 'required', 'on' => [self::SCENARIO_UPDATE_NORMAL_USER]],
            [['access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['mobile', 'shop_phone_number'], 'integer'],
            [['weight', 'height'], 'number'],
            [['personal_information', 'user_type', 'is_shop_owner'], 'string'],
            [['profile_picture', 'shop_logo', 'shop_address', 'password_hash', 'temporary_password', 'access_token', 'password_reset_token'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 60],
            [['shop_name', 'shop_email'], 'string', 'max' => 100],
            [['email', 'shop_email'], 'email'],
            [['email'], 'unique'],
            //['confirm_password', 'compare', 'skipOnEmpty' => false, 'compareAttribute' => 'password_hash', 'message' => "Passwords don't match"],
            ['confirm_password', 'compare', 'compareAttribute' => 'password_hash', 'message' => "Passwords don't match",],
            [['confirm_password'], 'safe'],
            //[['shop_logo', 'shop_phone_number', 'shop_name', 'shop_email', 'shop_address'], 'required',
            [['shop_logo', 'shop_phone_number', 'shop_name', 'shop_email', 'shop_address'], 'required',
                'when' => function ($model) {
                    //return ($model->is_shop_owner == "1");
                },
                'whenClient' => "function (attribute, value) {
                    if ($('#users-is_shop_owner').prop('checked') == true) {            
                                    return $('#users-shop_name').val() == '';
                                    return $('#users-shop_logo').val() == '';
                                    return $('#users-shop_address').val() == '';
                                    return $('#users-shop_phone_number').val() == '';
                                    return $('#users-shop_email').val() == '';
                                    }
                                }",],
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
            'password_hash' => 'Password Hash',
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
