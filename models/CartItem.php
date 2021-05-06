<?php

namespace app\models;

use \yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\modules\api\v1\models\User;
use Yii;

/**
 * This is the model class for table "cart_items".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity
 * @property float $price
 * @property string|null $color
 * @property int|null $size
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Users $user
 * @property Products $product
 */
class CartItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart_items';
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
            [['user_id', 'product_id', 'quantity', 'price'], 'required'],
            [['user_id', 'product_id', 'quantity', 'size'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['color'], 'string', 'max' => 100],
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
            'quantity' => 'Quantity',
            'price' => 'Price',
            'color' => 'Color',
            'size' => 'Size',
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
            'product'=>'product'
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
    //////////only for api/////////
     /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        // return $this->hasOne(User::className(), ['id' => 'user_id']);
        $userDetails = User::find()->where(['id'=> $this->user_id])->one();
        if($userDetails instanceof User){
            $profilepicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if(!empty($userDetails->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $userDetails->profile_picture)) {    
                $profilepicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $userDetails->profile_picture;
            }
            $userDetails->profile_picture = $profilepicture;
        }
        return $userDetails;
    }
}
