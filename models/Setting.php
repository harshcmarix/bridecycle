<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $option_key
 * @property string $option_value
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Setting extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'settings';
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
            [['created_at', 'updated_at','option_key','option_value'], 'safe'],
            [['option_key', 'option_value'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'option_key' => 'Transaction Fees',
            'option_value' => 'KM range to find the tailors',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
