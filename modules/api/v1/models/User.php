<?php

namespace app\modules\api\v1\models;

use app\models\BlockUser;
use app\models\Order;
use app\models\ProductRating;
use app\models\SellerRating;
use app\models\UserDevice;
use app\models\UserSubscription;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;
use app\modules\api\v1\models\{
    UserAddress
};
use app\models\ShopDetail;
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
 * @property string|null $mobile
 * @property float|null $weight
 * @property string|null $height
 * @property float|null $top_size
 * @property float|null $pant_size
 * @property float|null $bust_size
 * @property float|null $waist_size
 * @property float|null $hip_size
 * @property string|null $personal_information
 * @property string|null $user_type 1 => admin, 2 => sub admin, 3 => normal user
 * @property string $is_shop_owner 1 => shop owner
 * @property string|null $shop_cover_picture
 * @property string|null $shop_name
 * @property string|null $shop_email
 * @property string|null $shop_phone_number
 * @property string|null $shop_logo
 * @property string|null $website
 * @property string|null $verification_code
 * @property string|null $facebook_id
 * @property string|null $apple_id
 * @property string|null $google_id
 * @property string|null $latitude
 * @property string|null $longitude
 *
 * @property string|null $is_new_message_notification_on
 * @property string|null $is_offer_update_notification_on
 * @property string|null $is_offer_on_favourite_notification_on
 * @property string|null $is_saved_searches_notification_on
 * @property string|null $is_order_placed_notification_on
 * @property string|null $is_payment_done_notification_on
 * @property string|null $is_order_delivered_notification_on
 * @property string|null $is_click_and_try_notification_on
 *
 * @property string|null $is_new_message_email_notification_on
 * @property string|null $is_offer_update_email_notification_on
 * @property string|null $is_offer_on_favourite_email_notification_on
 * @property string|null $is_saved_searches_email_notification_on
 * @property string|null $is_order_placed_email_notification_on
 * @property string|null $is_payment_done_email_notification_on
 * @property string|null $is_order_delivered_email_notification_on
 * @property string|null $is_click_and_try_email_notification_on
 *
 * @property string|null $is_verify_user
 * @property string|null $is_newsletter_subscription
 *
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FavouriteProducts[] $favouriteProducts
 * @property Orders[] $orders
 * @property ProductRatings[] $productRatings
 * @property PromoCodes[] $promoCodes
 * @property UserAddresses[] $userAddresses
 * @property UserSocialIdentities[] $userSocialIdentities
 * @property UserSubscriptions[] $userSubscriptions
 * @property UserDevices[] $userDevices
 * @property UserDevice $userDevice
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Used for authentication
     * @var string
     */
    public $authKey;

    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    public $confirm_password;

    /**
     * variables for shop detail tables validation
     */
    public $shop_cover_picture;
    public $shop_name;
    public $shop_email;
    public $shop_phone_number;
    public $shop_logo;
    public $website;

    /**
     * Identify user type
     */
    const USER_TYPE_ADMIN = 1;
    const USER_TYPE_SUB_ADMIN = 2;
    const USER_TYPE_NORMAL = 3;

    const SCENARIO_SHOP_OWNER = 'shop_owner';
    const SCENARIO_ADD_SIZE_INFORMARION_FOR_NORMAL_USER = "add_size_info";
    const SHOP_OWNER_YES = '1';
    const SHOP_OWNER_NO = '0';
    const SCENARIO_USER_CREATE = 'create';
    const SCENARIO_USER_UPDATE = 'update';
    const PROFILE_PICTURE_UPDATE = 'profile_picture_update';

    const SCENARIO_API_NOTIFICATION_SETTING = 'notification_setting';
    const SCENARIO_USER_CREATE_FROM_SOCIAL = 'create_social';

    const IS_NOTIFICATION_ON = '1'; // is on
    const IS_NOTIFICATION_OFF = '0'; // is off

    const IS_EMAIL_NOTIFICATION_ON = '1'; // is on
    const IS_EMAIL_NOTIFICATION_OFF = '0'; // is off

    const IS_VERIFY_USER_NO = '0';
    const IS_VERIFY_USER_YES = '1';

    const IS_LOGIN_FROM_FACEBOOK = "facebook";
    const IS_LOGIN_FROM_APPLE = "apple";
    const IS_LOGIN_FROM_GOOGLE = "google";

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
            [['first_name', 'last_name', 'email'], 'required', 'on' => [self::SCENARIO_USER_CREATE, self::SCENARIO_USER_UPDATE, self::SCENARIO_SHOP_OWNER]],
            [['first_name'], 'required', 'on' => [self::SCENARIO_USER_CREATE_FROM_SOCIAL]], // 'last_name'
            [['password', 'confirm_password'], 'required', 'on' => [self::SCENARIO_USER_CREATE, self::SCENARIO_SHOP_OWNER]],
            [['top_size', 'pant_size', 'bust_size', 'waist_size', 'hip_size', 'height'], 'required', 'on' => self::SCENARIO_ADD_SIZE_INFORMARION_FOR_NORMAL_USER],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Confirm Password don't match"],
            [['facebook_id', 'apple_id', 'google_id', 'access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['mobile', 'shop_phone_number'], 'integer'],
            //[['mobile', 'shop_phone_number'], 'string'],
            [['personal_information', 'user_type', 'is_shop_owner'], 'string'],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email', 'shop_email'], 'email'],
            [['is_verify_user', 'latitude', 'longitude'], 'safe'],
            [['email'], 'unique','message'=>'This email is already taken. Please login with this email address.'],
            [['email'], 'string', 'max' => 60],
            [['verification_code'], 'string', 'max' => 6],
            [['password_hash', 'temporary_password', 'access_token', 'password_reset_token', 'website'], 'string', 'max' => 255],
            [['website'], 'url', 'defaultScheme' => ''],
            [['temporary_password'], 'string', 'max' => 8],
            [['shop_logo', 'profile_picture', 'shop_cover_picture'], 'file', 'extensions' => 'png,jpg'],
            [['shop_name', 'shop_email'], 'string', 'max' => 100],
            [['profile_picture'], 'required', 'on' => [self::PROFILE_PICTURE_UPDATE]],
            [['shop_name', 'shop_email', 'shop_logo'], 'required', 'on' => [self::SCENARIO_SHOP_OWNER]],
            [['weight', 'height', 'top_size', 'pant_size', 'bust_size', 'waist_size', 'hip_size'], 'number'],
            // [['weight', 'height', 'top_size', 'pant_size', 'bust_size', 'waist_size', 'hip_size'], 'string'],
            [['is_newsletter_subscription'], 'safe'],
            [['is_new_message_notification_on', 'is_offer_update_notification_on', 'is_offer_on_favourite_notification_on', 'is_saved_searches_notification_on', 'is_order_placed_notification_on', 'is_payment_done_notification_on', 'is_order_delivered_notification_on', 'is_click_and_try_notification_on'], 'safe'],
            [['is_new_message_email_notification_on', 'is_offer_update_email_notification_on', 'is_offer_on_favourite_email_notification_on', 'is_saved_searches_email_notification_on', 'is_order_placed_email_notification_on', 'is_payment_done_email_notification_on', 'is_order_delivered_email_notification_on', 'is_click_and_try_email_notification_on'], 'safe'],
            [['is_new_message_notification_on', 'is_offer_update_notification_on', 'is_offer_on_favourite_notification_on', 'is_saved_searches_notification_on', 'is_order_placed_notification_on', 'is_payment_done_notification_on', 'is_order_delivered_notification_on', 'is_click_and_try_notification_on', 'is_new_message_email_notification_on', 'is_offer_update_email_notification_on', 'is_offer_on_favourite_email_notification_on', 'is_saved_searches_email_notification_on', 'is_order_placed_email_notification_on', 'is_payment_done_email_notification_on', 'is_order_delivered_email_notification_on', 'is_click_and_try_email_notification_on'], 'required', 'on' => [self::SCENARIO_API_NOTIFICATION_SETTING]],
            [['shop_email'], 'unique', 'targetClass' => ShopDetail::ClassName(), 'targetAttribute' => ['shop_email']]
        ];
    }


    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password_hash'], $fields['temporary_password'], $fields['access_token'], $fields['access_token_expired_at'], $fields['password_reset_token']);
        return $fields;
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
            'top_size' => 'Top Size',
            'pant_size' => 'Pant Size',
            'bust_size' => 'Bust Size',
            'waist_size' => 'Waist Size',
            'hip_size' => 'Hip Size',
            'personal_information' => 'Personal Information',
            'user_type' => 'User Type',
            'is_shop_owner' => 'Is Shop Owner',
            'verification_code' => 'Verification Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'userAddresses' => 'userAddresses',
            'shopDetails' => 'shopDetails',
            'rating' => 'rating'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavouriteProducts()
    {
        return $this->hasMany(FavouriteProducts::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ShopDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopDetails()
    {
        // $data = $this->hasMany(ShopDetail::className(), ['user_id' => 'id']);

        $data = ShopDetail::find()->where(['user_id' => $this->id])->all();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $shopLogo = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                $shop_cover_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($value->shop_logo) && file_exists(Yii::getAlias('@shopLogoThumbRelativePath') . '/' . $value->shop_logo)) {
                    $shopLogo = Yii::$app->request->getHostInfo() . Yii::getAlias('@shopLogoThumbAbsolutePath') . '/' . $value->shop_logo;
                }
                $value->shop_logo = $shopLogo;
                if (!empty($value->shop_cover_picture) && file_exists(Yii::getAlias('@shopCoverPictureThumbRelativePath') . '/' . $value->shop_cover_picture)) {
                    $shop_cover_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@shopCoverPictureThumbAbsolutePath') . '/' . $value->shop_cover_picture;
                }
                $value->shop_cover_picture = $shop_cover_picture;

            }
        }

        return $data;
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ProductRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductRatings()
    {
        return $this->hasMany(ProductRating::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        //return $this->hasMany(UserAddress::className(), ['user_id' => 'id']);
        $result = UserAddress::find()->where(['user_id' => $this->id])->orderBy(['is_primary_address' => SORT_DESC])->all();

        if (!empty($result)) {
            foreach ($result as $key => $resultRow) {
                if (!empty($resultRow) && $resultRow instanceof UserAddress) {
                    $result[$key]['is_primary_address'] = (string)$resultRow->is_primary_address;
                }
            }
        }
        return $result;
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
        return $this->hasMany(UserSubscription::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserDevides]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserDevices()
    {
        return $this->hasMany(UserDevice::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDevice()
    {
        $device = UserDevice::find()->where(['user_id' => $this->id])->orderBy(['id' => SORT_DESC])->one();
        return $device;
    }

    /**
     * Uses for API
     */
    public function getVerificationCode()
    {
        Start:
        $code = substr(str_shuffle('0123456789'), 0, 6);
        //$model = self::find()->where(['verification_code' => $code, 'user_type' => self::USER_TYPE_NORMAL, 'is_shop_owner' => self::SHOP_OWNER_YES])->one();
        $model = self::find()->where(['verification_code' => $code, 'user_type' => self::USER_TYPE_NORMAL])->one();
        if (!empty($model)) {
            goto Start;
        }
        return $code;
    }

    /**
     * @return object
     */
    public function getRating()
    {
        //$modelRate['total_rated_count'] = (int)number_format(SellerRating::find()->where(['seller_id' => $this->id])->andWhere(['IN', 'status', [ProductRating::STATUS_APPROVE]])->count(), 1);
        $modelRate['total_rated_count'] = (int)number_format(SellerRating::find()->where(['seller_id' => $this->id])->count(), 1);
        $modelRate['over_all_rate'] = (int)SellerRating::find()->where(['seller_id' => $this->id])->average('rate');
        $modelRate['one_star_rate'] = (int)SellerRating::find()->where(['seller_id' => $this->id, 'rate' => SellerRating::ONE_STAR_RATE])->count();
        $modelRate['two_star_rate'] = (int)SellerRating::find()->where(['seller_id' => $this->id, 'rate' => SellerRating::TWO_STAR_RATE])->count();
        $modelRate['three_star_rate'] = (int)SellerRating::find()->where(['seller_id' => $this->id, 'rate' => SellerRating::THREE_STAR_RATE])->count();
        $modelRate['four_star_rate'] = (int)SellerRating::find()->where(['seller_id' => $this->id, 'rate' => SellerRating::FOUR_STAR_RATE])->count();
        $modelRate['five_star_rate'] = (int)SellerRating::find()->where(['seller_id' => $this->id, 'rate' => SellerRating::FIVE_STAR_RATE])->count();

        return (object)$modelRate;
    }

    /**
     * @return array
     */
    public function getBlockUsersId()
    {
        $sellerIds = [];
        $models = BlockUser::find()->select(['seller_id'])->where(['user_id' => $this->id])->indexBy('seller_id')->all();
        if (!empty($models)) {
            foreach ($models as $key => $model) {
                $sellerIds[] = $model->seller_id;
            }
        }
        return $sellerIds;
    }

    /************************************************************************/
    /************************* Identity functions **************************/
    /***********************************************************************/

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
        // return static::findOne(['access_token' => $token]);
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
        return self::find()->where(['email' => $email, 'user_type' => User::USER_TYPE_NORMAL])->one();
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
}
