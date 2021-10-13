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
 * @property string|null $bank_name
 * @property string|null $ifsc_code
 * @property string|null $account_holder_name
 * @property string $account_number
 * @property string $account_type
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

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'bank_name', 'ifsc_code', 'account_holder_name', 'account_type', 'account_number', 'confirm_account_number'], 'required', 'on' => self::SCENARIO_CREATE],
            [['user_id', 'bank_name', 'ifsc_code', 'account_holder_name', 'account_type', 'account_number'], 'required', 'on' => self::SCENARIO_UPDATE],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['bank_name', 'ifsc_code', 'account_holder_name', 'account_type'], 'string', 'max' => 255],
            [['account_number'], 'string', 'max' => 25],
            ['confirm_account_number', 'compare', 'compareAttribute' => 'account_number', 'message' => "Confirm Account Number don't match!"],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']]

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
            'bank_name' => 'Bank Name',
            'ifsc_code' => 'Ifsc Code',
            'account_holder_name' => 'Account Holder Name',
            'account_number' => 'Account Number',
            'confirm_account_number' => 'Confirm Account Number',
            'account_type' => 'Account Type',
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
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }
}
