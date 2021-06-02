<?php

namespace app\models;

use \yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\modules\api\v1\models\User;
use Yii;

/**
 * This is the model class for table "product_ratings".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property float $rating
 * @property string $review
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 * @property Product $product
 */
class ProductRating extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_ratings';
    }

    const FIVE_STAR_RATE = '5';
    const FOUR_STAR_RATE = '4';
    const THREE_STAR_RATE = '3';
    const TWO_STAR_RATE = '2';
    const ONE_STAR_RATE = '1';

    const STATUS_PENDING = '1';
    const STATUS_APPROVE = '2';
    const STATUS_DECLINE = '3';

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
            [['user_id', 'product_id', 'review'], 'required'],
            [['user_id', 'product_id'], 'integer'],
            [['rating'], 'number'],
            [['review'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => 'Product ID',
            'rating' => 'Rating',
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

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
}
