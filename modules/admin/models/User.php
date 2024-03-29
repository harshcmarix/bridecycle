<?php

namespace app\modules\admin\models;

use app\models\Order;
use app\models\ShopDetail;
use app\models\UserPurchasedSubscriptions;
use app\models\UserSubscription;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string|null $profile_picture
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $username
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
 * @property int $is_newsletter_subscription 1 => Yes, 0 => No
 * @property string|null $shop_name
 * @property string|null $shop_email
 * @property int|null $shop_phone_number
 * @property string|null $user_status
 * @property string|null $stripe_account_connect_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FavouriteProducts[] $favouriteProducts
 * @property Orders[] $orders
 * @property ProductRatings[] $productRatings
 * @property UserAddresses[] $userAddresses
 * @property UserSocialIdentities[] $userSocialIdentities
 * @property UserSubscriptions[] $userSubscriptions
 * @property UserSubscription $userSubscription
 * @property UserPurchasedSubscriptions[] $userPurchasedSubscriptions
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Identify auth key
     * @var string
     */
    public $authKey;

    const SCENARIO_CREATE_NORMAL_USER = 'create_normal_user';
    const SCENARIO_UPDATE_NORMAL_USER = 'update_normal_user';

    const IS_SHOP_OWNER_YES = '1';
    const IS_SHOP_OWNER_NO = '0';

    public $isShopOwner = [
        //' ' => 'Select',
        self::IS_SHOP_OWNER_YES => 'Yes',
        self::IS_SHOP_OWNER_NO => 'No'
    ];

    const IS_ACTIVE = '1';
    const IS_INACTIVE = '0';

    public $arrStatus = [
        self::IS_ACTIVE => 'Active',
        self::IS_INACTIVE => 'Inactive'
    ];

    /**
     * Identify user type
     */
    const USER_TYPE_ADMIN = '1';
    const USER_TYPE_SUB_ADMIN = '2';
    const USER_TYPE_NORMAL_USER = '3';

    const USER_STATUS_ACTIVE = '1';
    const USER_STATUS_IN_ACTIVE = '0';

    public $confirm_password;
    public $password;

    public $shop_address_street;
    public $shop_address_city;
    public $shop_address_state;
    public $shop_address_country;
    public $shop_address_zip_code;
    public $shop_logo;
    public $shop_phone_number;
    public $shop_name;
    public $shop_email;
    public $is_shop_logo_empty;

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

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'mobile'], 'required'],
            [['first_name', 'last_name', 'email', 'mobile', 'password', 'confirm_password', 'profile_picture', 'username'], 'required', 'on' => [self::SCENARIO_CREATE_NORMAL_USER]],
            [['first_name', 'last_name', 'email', 'mobile', 'username'], 'required', 'on' => [self::SCENARIO_UPDATE_NORMAL_USER]],

            [['profile_picture'], 'required', 'when' => function ($model) {

                return $model->scenario == self::SCENARIO_CREATE_NORMAL_USER;
            }, 'whenClient' => "function (attribute, value) {
                    if ($('#user-is_profile_picture_empty').val() == 1) {   

                        return $('#user-profile_picture').val() == '';                                    
                    }
                }",],


            [['stripe_account_connect_id'], 'string'],

            [['email', 'shop_email'], 'email'],
            [['user_status', 'access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['mobile', 'shop_phone_number'], 'string', 'max' => 13, 'min' => 10],
            [['weight', 'height', 'is_newsletter_subscription'], 'number'],
            [['personal_information', 'user_type', 'is_shop_owner'], 'string'],
            [['profile_picture', 'password_hash', 'temporary_password', 'access_token', 'password_reset_token'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 60],
            [['shop_name', 'shop_email'], 'string', 'max' => 100],
            [['shop_email'], 'unique', 'targetClass' => ShopDetail::class, 'targetAttribute' => ['shop_email'], 'filter' => ['!=', 'user_id', Yii::$app->request->get('id')], 'message' => 'Shop email already exist.'],
            [['email'], 'unique', 'message' => 'Email already exist.'],
            [['shop_logo'], 'file'],
            [['password', 'confirm_password'], 'string', 'min' => 6],
            [['password', 'confirm_password'], 'safe'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match",],

            [['shop_address_zip_code'], 'string', 'max' => 6],

            [['shop_logo'], 'required', 'when' => function ($model) {

            }, 'whenClient' => "function (attribute, value) {
                        if ($('#user-is_shop_logo_empty').val() == 1 && $('#user-is_shop_owner').prop('checked') == true) {
                            return $('#user-shop_logo').val() == '';                                    
                        }
                    }",],

            [['shop_logo', 'shop_phone_number', 'shop_name', 'shop_email', 'shop_address_street', 'shop_address_city', 'shop_address_state', 'shop_address_country', 'shop_address_zip_code'], 'required',
                'when' => function ($model) {
                },
                'whenClient' => "function (attribute, value) {
                        if ($('#user-is_shop_owner').prop('checked') == true) {            
                            return $('#user-shop_name').val() == '';
                            return $('#user-shop_logo').val() == '';
                            return $('#user-shop_address_street').val() == '';
                            return $('#user-shop_address_city').val() == '';
                            return $('#user-shop_address_state').val() == '';
                            return $('#user-shop_address_country').val() == '';
                            return $('#user-shop_address_zip_code').val() == '';
                            return $('#user-shop_phone_number').val() == '';
                            return $('#user-shop_email').val() == '';
                        }
                    }",],
            [['profile_picture'], 'file', 'extensions' => 'jpeg, jpg, png'],
            [['shop_logo'], 'file', 'extensions' => 'jpeg, jpg, png'],
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
            'username' => 'Username',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'password' => 'Password',
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
     * Gets query for [[ShopDetail]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopDetail()
    {
        return $this->hasOne(ShopDetail::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProducts::class, ['user_id' => 'id']);
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
        return $this->hasMany(ProductRatings::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddresses::class, ['user_id' => 'id']);
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

    /**
     * Gets query for [[UserSubscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserPurchasedSubscriptions()
    {
        return $this->hasMany(UserPurchasedSubscriptions::class, ['user_id' => 'id']);
    }

    /**
     * Check logged in user are admin or not.
     * @return boolean
     */
    public function isAdmin()
    {
        $role = $this->user_type;
        if ($role == self::USER_TYPE_ADMIN) {
            return true;
        }
        return false;
    }

    /************************************************************************************/
    /******************************* Identity Helper Functions **************************/
    /************************************************************************************/

    /**
     * @param int|string $id
     * @return User|IdentityInterface|null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return IdentityInterface|static|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::find()->where(['access_token' => $token])->one();

        if (!$user) {
            return false;
        }

        if ($user->access_token_expired_at < date('Y-m-d h:i:s', time())) {
            throw new UnauthorizedHttpException('The access token has been expired.');
        }

        return $user;
    }

    /**
     * @param $email
     * @return null|static
     */
    public static function findByEmail($email)
    {
        return self::find()->where(['email' => $email])->one();
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generate accessToken string
     * @return string
     * @throws \yii\base\Exception
     */
    public function generateAccessToken()
    {
        $this->access_token = \Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    /**
     * Generates password hash from password and sets it to the model.
     * @param $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = \Yii::$app->security->generatePasswordHash($password);
    }

    /************************************************************************************/
    /******************************* Reset Password Functions **************************/
    /************************************************************************************/

    /**
     * @param $token
     * @return User|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'user_type' => User::USER_TYPE_ADMIN
        ]);
    }

    /**
     * @param $token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['password_reset_token_expire_time'];
        return $timestamp + $expire >= time();
    }

    /**
     * @throws \yii\base\Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Remove password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscription()
    {
        return $this->hasOne(UserSubscription::class, ['user_id' => 'id']);
    }

    /**
     * @param $attribute
     */
    public function is13NumbersOnly($attribute)
    {
        if (!preg_match('/^[0-9]{10,13}$/', $this->$attribute)) {
            $this->addError($attribute, 'Mobile number must contain 10 to 13 digits.');
        }
    }
}
