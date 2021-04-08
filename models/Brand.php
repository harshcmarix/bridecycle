<?php

namespace app\models;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "brands".
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string $is_top_brand 1 => top brand
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Products[] $products
 */
class Brand extends \yii\db\ActiveRecord
{
    const SCENARIO_BRAND_CREATE = 'create';
    const BRAND_IMAGE_EMPTY = '1';
    const IS_TOP_BRAND_OR_NOT = [
        '1' => 'yes',
        '0' => 'no',
    ];
    public $is_brand_image_empty;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'brands';
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
            [['name'], 'required'],
            [['is_top_brand'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['image'], 'string', 'max' => 250],
            [['image'], 'file', 'extensions' => 'png,jpg'],
            [['image'], 'required', 'on' => self::SCENARIO_BRAND_CREATE],
            [['image'], 'required', 'when' => function ($model) {
                //return $model->is_brand_image_empty == '1';
            },'whenClient' => "function (attribute, value) {
                    if ($('#brand-is_brand_image_empty').val() == 1) {            
                                    return $('#brand-image').val() == '';                                    
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
            'name' => 'Name',
            'image' => 'Image',
            'is_top_brand' => 'Is Top Brand',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::className(), ['brand_id' => 'id']);
    }
}