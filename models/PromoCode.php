<?php

namespace app\models;

use \yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\modules\admin\models\User;
use Yii;

/**
 * This is the model class for table "promo_codes".
 *
 * @property int $id
 * @property string $code
 * @property int $user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Users $user
 */
class PromoCode extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promo_codes';
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

    //Promo code type
    const TYPE_FLAT = 'flat';
    const TYPE_DISCOUNT = 'discount';
    const ARR_PROMOCODE_TYPE = [
        self::TYPE_FLAT => 'Flat',
        self::TYPE_DISCOUNT => 'Discount'
    ];

    //Promo code status
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const ARR_PROMOCODE_STATUS = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active'
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'user_id', 'type', 'value', 'start_date', 'end_date'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 100],
            [['value'], 'double'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['start_date','end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['start_date', 'end_date'],'validateDates'],
        ];
    }

    public function validateDates(){
        if(strtotime($this->end_date) <= strtotime($this->start_date)) {
            $this->addError('end_date','End date should not less than or equal to start date');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Promo Code',
            'user_id' => 'User ID',
            'type' => 'Promo Code Type',
            'value' => 'Promo Code Value',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
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
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
