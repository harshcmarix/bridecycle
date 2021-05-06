<?php

namespace app\modules\api\v1\models;

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
 * @property float|null $height
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
    const SHOP_OWNER_YES = '1';
    const SHOP_OWNER_NO = '0';
    const SCENARIO_USER_CREATE = 'create';
    const SCENARIO_USER_UPDATE = 'update';

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
            [['password', 'confirm_password'], 'required', 'on' => [self::SCENARIO_USER_CREATE, self::SCENARIO_SHOP_OWNER]],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Confirm Password don't match"],
            [['access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['mobile', 'shop_phone_number'], 'integer'],
            [['personal_information', 'user_type', 'is_shop_owner'], 'string'],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email','shop_email'], 'email'],
            [['email'], 'unique'],
            [['email'], 'string', 'max' => 60],
            [[ 'password_hash', 'temporary_password', 'access_token', 'password_reset_token','website'], 'string', 'max' => 255],
            [['website'],'url', 'defaultScheme' => ''],
            [['temporary_password'], 'string', 'max' => 8],
            // [['shop_cover_picture'], 'file', 'extensions' => 'png,jpg'],
            // [['profile_picture','shop_cover_picture','shop_logo'], 'file', 'extensions' => 'png,jpg'],
            [['shop_logo','profile_picture','shop_cover_picture'], 'file', 'extensions' => 'png,jpg'],
            [['shop_name', 'shop_email'], 'string', 'max' => 100],
            [['shop_name', 'shop_email','shop_logo'], 'required', 'on' => [self::SCENARIO_SHOP_OWNER]],
            [['weight', 'height', 'top_size', 'pant_size', 'bust_size', 'waist_size', 'hip_size'], 'number'],
            [['shop_email'], 'unique','targetClass' => ShopDetail::ClassName() ,'targetAttribute' => ['shop_email']]
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
            'shopDetails'=>'shopDetails'
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
        if(!empty($data)){
            foreach($data as $key=>$value){
                $shopLogo = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                $shop_cover_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                  if(!empty($value->shop_logo)  && file_exists(Yii::getAlias('@shopLogoThumbRelativePath') . '/' . $value->shop_logo)){
                       $shopLogo = Yii::$app->request->getHostInfo() . Yii::getAlias('@shopLogoThumbAbsolutePath') . '/' . $value->shop_logo;
                  }
                  $value->shop_logo = $shopLogo;
                  if(!empty($value->shop_cover_picture) && file_exists(Yii::getAlias('@shopCoverPictureThumbRelativePath') . '/' . $value->shop_cover_picture)){
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
        return $this->hasMany(UserAddress::className(), ['user_id' => 'id']);
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
