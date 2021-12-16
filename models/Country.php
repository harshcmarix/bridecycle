<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "country".
 *
 * @property int $id
 * @property int $name
 * @property int $code
 * @property int $under_continent
 * @property int $created_at
 * @property int $updated_at
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'country';
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

    const CONTINENT_ASIA = 'asia';
    const CONTINENT_AUSTRALIA = 'australia';
    const CONTINENT_ANTARCTICA = 'antarctica';
    const CONTINENT_AFRICA = 'africa';
    const CONTINENT_EUROPE = 'europe';
    const CONTINENT_NORTH_AMERICA = 'north_america';
    const CONTINENT_SOUTH_AMERICA = 'south_america';

    public $arrContinent = [
        self::CONTINENT_ASIA => "Asia",
        self::CONTINENT_AUSTRALIA => "Australia",
        self::CONTINENT_ANTARCTICA => "Antarctica",
        self::CONTINENT_AFRICA => "Africa",
        self::CONTINENT_EUROPE => "Europe",
        self::CONTINENT_NORTH_AMERICA => "North America",
        self::CONTINENT_SOUTH_AMERICA => "South America",
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code', 'under_continent'], 'required'],
            [['name', 'code', 'under_continent', 'created_at', 'updated_at'], 'safe'],
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
            'code' => 'Google Country Code',
            'under_continent' => 'Under Continent',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
