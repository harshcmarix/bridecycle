<?php

namespace app\models;

use Yii;
use app\modules\api\v2\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "payment_transfer_details".
 *
 * @property int $id
 * @property int|null $order_id
 * @property int|null $seller_id
 * @property string|null $transfer_amount
 * @property string|null $source_id
 * @property string|null $destination_id
 * @property int $is_transferred '0'=>'no','1'=>'yes'
 * @property string|null $transfer_status
 * @property string|null $transfer_id
 * @property string|null $transfer_response
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Order $order
 * @property User $seller
 */
class PaymentTransferDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_transfer_details';
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

    const IS_TRANSFFERED_YES = '1';
    const IS_TRANSFFERED_NO = '0';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'seller_id', 'is_transferred'], 'integer'],
            [['transfer_response'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['transfer_amount'], 'string', 'max' => 20],
            [['source_id', 'destination_id', 'transfer_id'], 'string', 'max' => 255],
            [['transfer_status'], 'string', 'max' => 25],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['seller_id' => 'id']],
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
            'transfer_amount' => 'Transfer Amount',
            'source_id' => 'Source ID',
            'destination_id' => 'Destination ID',
            'is_transferred' => 'Is Transferred',
            'transfer_status' => 'Transfer Status',
            'transfer_id' => 'Transfer ID',
            'transfer_response' => 'Transfer Response',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
        $data = User::find()->where(['id' => $this->seller_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        $shopDetail['shopDetail'] = (!empty($data->shopDetails)) ? $data->shopDetails : null;
        $data = array_merge($data->toArray(), $shopDetail);
        return $data;
    }
}