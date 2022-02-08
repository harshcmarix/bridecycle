<?php

namespace app\models;

use \yii\db\ActiveRecord;
use app\modules\api\v2\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "searches".
 *
 * @property int $id
 * @property int $user_id
 * @property string $search_text
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class SearchHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'search_histories';
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
            [['user_id'], 'required', 'message' => getValidationErrorMsg('user_id_required', \Yii::$app->language)],
            [['search_text'], 'required', 'message' => getValidationErrorMsg('search_text_required', \Yii::$app->language)],
            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', \Yii::$app->language)],
            [['created_at', 'updated_at'], 'safe'],
            [['search_text'], 'string', 'max' => 255],
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
            'search_text' => 'Search Text',
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
            'user' => 'user',
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
