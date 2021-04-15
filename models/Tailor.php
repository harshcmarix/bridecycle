<?php

namespace app\models;

use \yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "tailors".
 *
 * @property int $id
 * @property string $name
 * @property string $shop_name
 * @property string $shop_image
 * @property string $address
 * @property int|null $mobile
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Tailor extends ActiveRecord
{
    /**
     * Used for create
     */
    const SCENARIO_CREATE = 'create';
    /**
     * used to check image is empty or not in validation
     */
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    public $is_shop_image_empty;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tailors';
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
            [['name', 'shop_name', 'address'], 'required'],
            [['address'], 'string'],
            [['mobile'], 'integer'],
            [['shop_image'], 'file', 'extensions' => 'png,jpg'],
            [['shop_image'],'required','on'=>self::SCENARIO_CREATE],
            [['shop_image'], 'required', 'when' => function ($model) {
                //return $model->is_brand_image_empty == '1';
            },'whenClient' => "function (attribute, value) {
                    if ($('#tailor-is_shop_image_empty').val() == 1) {            
                                    return $('#tailor-shop_image').val() == '';                                    
                                    }
            }",],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'shop_image'], 'string', 'max' => 255],
            [['shop_name'], 'string', 'max' => 50],
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
            'shop_name' => 'Shop Name',
            'shop_image' => 'Shop Image',
            'address' => 'Address',
            'mobile' => 'Mobile',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
