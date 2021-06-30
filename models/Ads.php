<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "ads".
 *
 * @property int $id
 * @property string $title
 * @property string $image
 * @property string $url
 * @property int $status '1'=>'inactive','2'=>'active'
 * @property string $created_at
 * @property string|null $updated_at
 */
class Ads extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ads';
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
     * used for create
     */
    const SCENARIO_CREATE = 'create';

    /**
     * used for image validation
     */
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    public $is_ads_image_empty;


    const STATUS_INACTIVE = 1;
    const STATUS_ACTIVE = 2;


    const ARR_ADS_STATUS = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active'
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url', 'status'], 'required'],
            [['url'], 'url'],
            [['status'], 'integer'],
            [['image'], 'file', 'extensions' => 'png, jpg, jpeg'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['image'], 'required', 'on' => self::SCENARIO_CREATE],
            [['image'], 'required', 'when' => function ($model) {
                return $model->scenario == self::SCENARIO_CREATE;
            }, 'whenClient' => "function (attribute, value) {
                    if ($('#ads-is_ads_image_empty').val() == 1) {   
                    alert(hii);         
                                    return $('#ads-image').val() == '';                                    
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
            'title' => 'Title',
            'image' => 'Image',
            'url' => 'Url',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
