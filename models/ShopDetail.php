<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use app\modules\admin\models\User;

/**
 * This is the model class for table "shop_details".
 *
 * @property int $id
 * @property int $user_id
 * @property string $shop_cover_picture
 * @property string $shop_name
 * @property string $shop_email
 * @property string $shop_phone_number
 * @property string $shop_logo
 * @property string $website
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class ShopDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shop_details';
    }
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
            [['shop_cover_picture', 'shop_name', 'shop_email', 'shop_phone_number', 'shop_logo', 'website'], 'required'],
            [['user_id'], 'integer'],
            [['shop_email'], 'email'],
            //[['shop_email'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
            [[ 'shop_name', 'shop_email', 'website'], 'string', 'max' => 255],
            [['shop_phone_number'], 'string', 'max' => 15],
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
            'shop_cover_picture' => 'Shop Cover Picture',
            'shop_name' => 'Shop Name',
            'shop_email' => 'Shop Email',
            'shop_phone_number' => 'Shop Phone Number',
            'shop_logo' => 'Shop Logo',
            'website' => 'Website',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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

}
