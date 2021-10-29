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
 * @property string $name
 * @property int|null $brand_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Brand $brand
 * @property Brand $brand0
 * @property product $product
 *
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
            [['name', 'brand_id', 'created_at', 'updated_at'], 'safe'],
            [['name',], 'required'], //'brand_id'
            [['image',], 'string', 'max' => 255],
            [['image'], 'file', 'extensions' => 'png,jpg'],
            [['image'], 'required', 'on' => self::SCENARIO_CREATE],
            [['image'], 'required', 'when' => function ($model) {
                //return $model->is_brand_image_empty == '1';
            }, 'whenClient' => "function (attribute, value) {
                    if ($('#banner-is_banner_image_empty').val() == 1) {            
                                    return $('#banner-image').val() == '';                                    
                                    }
            }",],
            [['image'], 'file', 'extensions' => 'jpg, png'],
            [['brand_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Brand::className(), 'targetAttribute' => ['brand_id' => 'id']],
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
            'image' => 'Image',
            'brand_id' => 'Brand',
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
            'brand0' => 'brand0',
            'product0' => 'product0'
        ];
    }


    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::class, ['id' => 'brand_id']);
    }

///////////////////////For api use only /////////////////////////////////////////////

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand0()
    {
        $brand = Brand::find()->where(['id' => $this->brand_id])->one();
        if ($brand instanceof Brand) {
            $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($brand->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $brand->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageAbsolutePath') . '/' . $brand->image;
            }
            $brand->image = $brandImage;
        }
        return $brand;
    }
}
