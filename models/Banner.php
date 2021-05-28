<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "banners".
 *
 * @property int $id
 * @property string $image
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Banner extends ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners';
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
            [['created_at', 'updated_at'], 'safe'],
            [['image'], 'string', 'max' => 255],
            [['image'], 'file', 'extensions' => 'png,jpg'],
            [['image'], 'required','on'=>self::SCENARIO_CREATE],
            [['image'], 'required', 'when' => function ($model) {
                //return $model->is_brand_image_empty == '1';
            },'whenClient' => "function (attribute, value) {
                    if ($('#banner-is_banner_image_empty').val() == 1) {            
                                    return $('#banner-image').val() == '';                                    
                                    }
            }",],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
