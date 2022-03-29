<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\api\v2\models\User;

/**
 * This is the model class for table "order_return".
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $seller_id
 * @property int|null $buyer_id
 * @property int $is_other_reason
 * @property string $reason
 * @property string|null $description
 * @property string|null $image_one
 * @property string|null $image_two
 * @property int $status => 0=pending,1=accept,2=decline
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 * @property User $seller
 * @property User $buyer
 */
class OrderReturn extends \yii\db\ActiveRecord
{

    public $images;

    const IS_RETURN_APPROVED_YES = '1';
    const IS_RETURN_APPROVED_NO = '0';

    const STATUS_PENDING = '0';
    const STATUS_ACCEPT = '1';
    const STATUS_DECLINE = '2';

    const IS_RETURN_OTHER_REASON_YES = '1';
    const IS_RETURN_OTHER_REASON_NO = '0';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_return';
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
            //[['order_id', 'reason', 'description', 'image_one', 'image_two'], 'required'],
            [['order_id'], 'required', 'message' => getValidationErrorMsg('order_id_required', Yii::$app->language)],
            [['reason'], 'required', 'message' => getValidationErrorMsg('return_reason_required', Yii::$app->language)],
            [['order_id', 'seller_id', 'buyer_id', 'is_other_reason', 'status'], 'integer'],
            [['reason', 'description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['image_one', 'image_two'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['seller_id' => 'id']],
            [['buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['buyer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'seller_id' => 'Seller ID',
            'buyer_id' => 'Buyer ID',
            'is_other_reason' => 'Is Other Reason',
            'reason' => 'Reason',
            'description' => 'Description',
            'image_one' => 'Image One',
            'image_two' => 'Image Two',
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
            'order' => 'order',
            'seller' => 'seller',
            'buyer' => 'buyer',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
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
     * Gets query for [[Buyer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(User::class, ['id' => 'buyer_id']);
    }
}
