<?php

namespace app\models;

/**
 * This is the model class for table "timezones".
 *
 * @property int $id
 * @property string $time_zone
 * @property string $created_at
 * @property string|null $updated_at
 */
class Timezone extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'timezones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['time_zone'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['time_zone'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'time_zone' => 'Time Zone',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
