<?php

namespace app\models;

use app\modules\api\v1\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "block_user".
 *
 * @property int $id
 * @property int $user_id
 * @property int $seller_id
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $seller
 * @property User $user
 */
class BlockUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'block_user';
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
            [['user_id', 'seller_id'], 'required'],
            [['user_id', 'seller_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['seller_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'seller_id' => 'Seller ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'user0' => 'user0',
            'seller0' => 'seller0',
        ];
    }

    /**
     * Gets query for [[Seller]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeller()
    {
        return $this->hasOne(User::className(), ['id' => 'seller_id']);
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

    /////////api use only/////////////

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        //return $this->hasOne(User::className(), ['id' => 'user_id']);
        $user = User::find()->where(['id' => $this->user_id])->one();
        if ($user instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($user->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $user->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $user->profile_picture;
            }
            $user->profile_picture = $profilePicture;
        }
        return $user;
    }

    /**
     * Gets query for [[Seller]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeller0()
    {
        //return $this->hasOne(User::className(), ['id' => 'seller_id']);
        $seller = User::find()->where(['id' => $this->seller_id])->one();
        if ($seller instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($seller->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $seller->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $seller->profile_picture;
            }
            $seller->profile_picture = $profilePicture;
        }
        return $seller;
    }
}
