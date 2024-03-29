<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_bank_details".
 *
 * @property int $id
 * @property int $user_id
 * @property string $debit_card
 * @property string $first_name
 * @property string $last_name
 * @property string $country
 * @property string $iban
 * @property string $billing_address_line_1
 * @property string|null $billing_address_line_2
 * @property string $city
 * @property string $post_code
 * @property string $payment_type
 * @property string|null $paypal_email
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class UserBankDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_bank_details';
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
     * @var string
     */
    public $confirm_account_number;

    /**
     * Constants
     */
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    const PAYMENT_TYPE_BANK = "bank";
    const PAYMENT_TYPE_PAYPAL = "paypal";

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required', 'message' => getValidationErrorMsg('user_id_required', Yii::$app->language)],
            [['payment_type'], 'required', 'message' => getValidationErrorMsg('payment_type_required', Yii::$app->language)],
            [['user_id'], 'required', 'on' => self::SCENARIO_CREATE, 'message' => getValidationErrorMsg('user_id_required', Yii::$app->language)],
            [['payment_type'], 'required', 'on' => self::SCENARIO_UPDATE, 'message' => getValidationErrorMsg('payment_type_required', Yii::$app->language)],
            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', Yii::$app->language)],

            [['created_at', 'updated_at', 'post_code'], 'safe'],

            [['debit_card', 'first_name', 'last_name', 'country', 'billing_address_line_1', 'billing_address_line_2', 'city', 'paypal_email'], 'string', 'max' => 255],
            [['iban'], 'string', 'max' => 100],

            [['debit_card'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('debit_card_required', Yii::$app->language)],
            [['first_name'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('first_name_required', Yii::$app->language)],
            [['last_name'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('last_name_required', Yii::$app->language)],
            [['country'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('country_required', Yii::$app->language)],
            [['iban'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('iban_required', Yii::$app->language)],
            [['billing_address_line_1'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('billing_address_line_1_required', Yii::$app->language)],
            [['billing_address_line_2'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('billing_address_line_2_required', Yii::$app->language)],
            [['city'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('city_required', Yii::$app->language)],
            [['post_code'], 'required', 'on' => [self::PAYMENT_TYPE_BANK], 'message' => getValidationErrorMsg('post_code_required', Yii::$app->language)],

            [['paypal_email'], 'required', 'on' => [self::PAYMENT_TYPE_PAYPAL], 'message' => getValidationErrorMsg('paypal_email_required', Yii::$app->language)],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']]
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
            'debit_card' => 'Debit Card',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'country' => 'Country',
            'iban' => 'IBAN',
            'billing_address_line_1' => 'Billing Address Line 1',
            'billing_address_line_2' => 'Billing Address Line 2',
            'city' => 'Town/City',
            'post_code' => 'Post Code',
            'payment_type' => 'Payment Type',
            'paypal_email' => 'Paypal Email',
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
            'user0' => 'user0',
        ];
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

    ///////////////////////For api use only /////////////////////////////////////////////

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getUser0()
    {
        $data = User::find()->where(['id' => $this->user_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }elseif (!empty($data->social_media_profile_picture)) {
                $profilePicture = $data->social_media_profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

}
