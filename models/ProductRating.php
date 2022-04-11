<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_ratings".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property float $rating
 * @property string $review
 * @property string $status
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

    /**
     * Constants
     */
    const FIVE_STAR_RATE = '5';
    const FOUR_STAR_RATE = '4';
    const THREE_STAR_RATE = '3';
    const TWO_STAR_RATE = '2';
    const ONE_STAR_RATE = '1';

    const STATUS_PENDING = '1';
    const STATUS_APPROVE = '2';
    const STATUS_DECLINE = '3';

    const ARR_PRODUCT_RATING_STATUS = [
        self::STATUS_PENDING => 'Pending Approval',
        self::STATUS_APPROVE => ' Approved',
        self::STATUS_DECLINE => ' Decline'
    ];

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
            [['user_id'], 'required', 'message' => getValidationErrorMsg('user_id_required', Yii::$app->language)],
            [['product_id'], 'required', 'message' => getValidationErrorMsg('product_id_required', Yii::$app->language)],
            [['review'], 'required', 'message' => getValidationErrorMsg('review_required', Yii::$app->language)],

            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', Yii::$app->language)],
            [['product_id'], 'integer', 'message' => getValidationErrorMsg('product_id_integer_validation', Yii::$app->language)],

            [['rating'], 'number', 'message' => getValidationErrorMsg('rating_number_validation', Yii::$app->language)],
            [['review'], 'string'],

            [['status', 'created_at', 'updated_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'product' => 'product',
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

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /////////api use only/////////////

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        $user = User::find()->where(['id' => $this->user_id])->one();
        if ($user instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($user->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $user->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $user->profile_picture;
            }elseif (!empty($user->social_media_profile_picture)) {
                $profilePicture = $user->social_media_profile_picture;
            }
            $user->profile_picture = $profilePicture;
        }
        return $user;
    }

}
