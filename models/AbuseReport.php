<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "abuse_report".
 *
 * @property int $id
 * @property int $user_id
 * @property int $seller_id
 * @property string $content
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $seller
 * @property User $user
 */
class AbuseReport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'abuse_report';
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
            [['user_id', 'seller_id', 'content'], 'required'],
            [['user_id', 'seller_id'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['seller_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'content' => 'Content',
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
        return $this->hasOne(User::class, ['id' => 'seller_id']);
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

    /////////api use only/////////////

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        //return $this->hasOne(User::class, ['id' => 'user_id']);
        $user = User::find()->where(['id' => $this->user_id])->one();
        if ($user instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($user->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $user->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $user->profile_picture;
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
        //return $this->hasOne(User::class, ['id' => 'seller_id']);
        $seller = User::find()->where(['id' => $this->seller_id])->one();
        if ($seller instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($seller->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $seller->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $seller->profile_picture;
            }
            $seller->profile_picture = $profilePicture;
        }
        return $seller;
    }
}
