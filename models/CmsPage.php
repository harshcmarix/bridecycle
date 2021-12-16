<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cms_pages".
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class CmsPage extends ActiveRecord
{
    const MIN_DESCRIPTION_TEXT = 0;
    const MAX_DESCRIPTION_TEXT = 100;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cms_pages';
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
            [['title', 'description'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'slug' => 'Slug',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
