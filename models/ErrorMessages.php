<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "error_messages".
 *
 * @property int $id
 * @property string $error_key
 * @property string|null $english_message
 * @property string|null $german_message
 */
class ErrorMessages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'error_messages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['error_key'], 'required'],
            [['error_key'], 'unique'],
            [['english_message', 'german_message'], 'string'],
            [['error_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'error_key' => 'Error Key',
            'english_message' => 'English Message',
            'german_message' => 'German Message',
        ];
    }
}
