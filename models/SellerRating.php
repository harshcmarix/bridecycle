<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "seller_rating".
 *
 * @property int $id
 * @property int $seller_id
 * @property int $user_id
 * @property float $rate
 * @property string $review
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $seller
 * @property User $user
 */
class SellerRating extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'seller_rating';
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


    const FIVE_STAR_RATE = '5';
    const FOUR_STAR_RATE = '4';
    const THREE_STAR_RATE = '3';
    const TWO_STAR_RATE = '2';
    const ONE_STAR_RATE = '1';


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seller_id', 'user_id', 'rate', 'review'], 'required'],
            [['seller_id', 'user_id'], 'integer'],
            [['rate'], 'number'],
            [['review'], 'string'],
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
            'seller_id' => 'Seller ID',
            'user_id' => 'User ID',
            'rate' => 'Rate',
            'review' => 'Review',
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
