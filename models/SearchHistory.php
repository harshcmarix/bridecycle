<?php

namespace app\models;

use \yii\db\ActiveRecord;
use app\modules\api\v2\models\User;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "searches".
 *
 * @property int $id
 * @property int $user_id
 * @property string $search_text
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Users $user
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
            [['user_id', 'search_text'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['search_text'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
