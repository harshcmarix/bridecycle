<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\api\v2\models\User;

/**
 * This is the model class for table "make_offer".
 *
 * @property int $id
 * @property int $product_id
 * @property int $sender_id
 * @property int $receiver_id
 * @property float $offer_amount
 * @property int $status '1'=>'pending','2'=>'accept','3'=>'reject'
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 * @property User $sender
 * @property User $receiver
 */
class MakeOffer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'make_offer';
    }

    public $offered_count;

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

    const STATUS_PENDING = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_REJECT = 3;

    const USER_ALLOWED_OFFER = 3;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'sender_id', 'receiver_id', 'status'], 'required'],
            [['product_id', 'sender_id', 'receiver_id', 'status'], 'integer'],
            [['offer_amount'], 'number'],
            [['offered_count', 'created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['receiver_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'sender_id' => 'Sender ID',
            'receiver_id' => 'Receiver ID',
            'offer_amount' => 'Offer Amount',
            'status' => 'Status',
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
            'product0' => 'product0',
            'offerCount' => 'offerCount',
            'sender0' => 'sender0',
            'receiver0' => 'receiver0'
        ];
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

    /**
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    /**
     * Gets query for [[Receiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'receiver_id']);
    }


    //////    API USES //////////////////////////////////////////////////////////

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct0()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * @return bool|int|string|null
     */
    public function getOfferCount()
    {
        return $this->offered_count = MakeOffer::find()
            ->where('make_offer.sender_id=' . $this->sender_id)
            ->andWhere('make_offer.product_id=' . $this->product_id)
            ->count();
    }

    /**
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender0()
    {
        $data = User::find()->where(['id' => $this->sender_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

    /**
     * Gets query for [[Receiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver0()
    {
        $data = User::find()->where(['id' => $this->receiver_id])->one();
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
