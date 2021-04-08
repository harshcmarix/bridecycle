<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "product_categories".
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property int|null $parent_category_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Products[] $products
 * @property Products[] $products0
 */
class ProductCategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_category_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['image'], 'string', 'max' => 255],
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
            'parent_category_id' => 'Parent Category ID',
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
        return $this->hasMany(Products::className(), ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Products0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts0()
    {
        return $this->hasMany(Products::className(), ['sub_category_id' => 'id']);
    }


    /**
     * Gets query for [[Products0]].
     *
     * @return \yii\db\ActiveQuery
     */
//    public function getParentCategory()
//    {
//        $data = ProductCategories::find()->where(['parent_category_id' => $this->id])->one();
//        return $data;
//    }
}
