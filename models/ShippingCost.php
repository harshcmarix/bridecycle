<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "shipping_cost".
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property string $created_at
 * @property string|null $updated_at
 */
class ShippingCost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shipping_cost';
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
     *  Constants
     */
    const CONTINENT_ASIA = 'Asia';
    const CONTINENT_CANADA = 'Canada';
    const CONTINENT_EUROPE = 'Europe';
    const CONTINENT_OTHER = 'Other';
    const CONTINENT_USA = 'USA';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'price'], 'required'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'price' => 'Price',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
