<?php

namespace app\modules\api\v1\models;

use yii\db\ActiveRecord;
use yii\web\{
    IdentityInterface,
    UnauthorizedHttpException
};

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $password_hash
 * @property string|null $temporary_password
 * @property string|null $access_token
 * @property string|null $access_token_expired_at
 * @property int|null $mobile
 * @property string|null $user_type
 * @property string $is_shop_owner
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
     * Identify user type
     */
    const USER_TYPE_ADMIN = 1;
    const USER_TYPE_SUB_ADMIN = 2;
    const USER_TYPE_NORMAL = 3;

    const SCENARIO_SHOP_OWNER = 'shop_owner';
    const SHOP_OWNER_YES = '1';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'password'], 'required', 'on' => 'create'],
            [['access_token_expired_at', 'created_at', 'updated_at'], 'safe'],
            [['mobile', 'shop_phone_number'], 'integer'],
            [['user_type', 'is_shop_owner'], 'string'],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 60],
            [['password_hash', 'access_token'], 'string', 'max' => 255],
            [['temporary_password'], 'string', 'max' => 8],
            [['profile_picture'], 'file', 'extensions' => 'jpg, png'],
//            [['shop_name', 'shop_email'], 'string', 'max' => 100 ,'required','on' => self::SCENARIO_SHOP_OWNER],
        ];
    }


    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password_hash'], $fields['temporary_password'], $fields['access_token'], $fields['access_token_expired_at']);
        return $fields;
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
            'password_hash' => 'Password Hash',
            'temporary_password' => 'Temporary Password',
            'access_token' => 'Access Token',
            'access_token_expired_at' => 'Access Token Expired At',
            'mobile' => 'Mobile',
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
