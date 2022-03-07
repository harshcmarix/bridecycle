<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "color".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $german_name
 * @property string $code
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 */
class Color extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'color';
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

    const SCENARIO_ADD_COLOR = 'create_color_admin';
    const SCENARIO_UPDATE_COLOR = 'update_color_admin';

    const STATUS_PENDING_APPROVAL = 1;
    const STATUS_APPROVE = 2;
    const STATUS_DECLINE = 3;

    public $arrStatus = [
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVE => ' Approved',
        self::STATUS_DECLINE => ' Decline'
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required', 'message' => getValidationErrorMsg('name_required', \Yii::$app->language)],

            [['name'], 'required', 'on' => self::SCENARIO_ADD_COLOR, 'message' => getValidationErrorMsg('name_required', \Yii::$app->language)],
            [['code'], 'required', 'on' => self::SCENARIO_ADD_COLOR, 'message' => getValidationErrorMsg('code_required', \Yii::$app->language)],

            [['name'], 'required', 'on' => self::SCENARIO_UPDATE_COLOR, 'message' => getValidationErrorMsg('name_required', \Yii::$app->language)],
            [['code'], 'required', 'on' => self::SCENARIO_UPDATE_COLOR, 'message' => getValidationErrorMsg('code_required', \Yii::$app->language)],

            [['status', 'created_at', 'updated_at', 'german_name'], 'safe'],
            [['name'], 'string', 'max' => 100, 'tooLong' => getValidationErrorMsg('name_max_100_character_length', \Yii::$app->language)],
            [['code'], 'string', 'max' => 15, 'tooLong' => getValidationErrorMsg('code_max_15_character_length', \Yii::$app->language)],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'german_name' => 'German Name',
            'code' => 'Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
