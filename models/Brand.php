<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "brands".
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string $is_top_brand 1 => top brand
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Product[] $products
 */
class Brand extends ActiveRecord
{
    /**
     * used for create
     */
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_API = 'create_api';

    /**
     * used for image validation
     */
    const IMAGE_EMPTY = 1;
    const IMAGE_NOT_EMPTY = 0;
    public $is_brand_image_empty;

    /**
     * use to identify top brand or not
     */
    const TOP_BRAND = '1';
    const NOT_TOP_BRAND = '0';
    /**
     * used for dropdown
     */
    const IS_TOP_BRAND_OR_NOT = [
        self::TOP_BRAND => 'yes',
        self::NOT_TOP_BRAND => 'no',
    ];

    const STATUS_PENDING_APPROVAL = 1;
    const STATUS_APPROVE = 2;
    const STATUS_DECLINE = 3;

    const ARR_BRAND_STATUS = [
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVE => ' Approved',
        self::STATUS_DECLINE => ' Decline'
    ];

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
            [['created_at', 'updated_at', 'status'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique'],
            // [['image'], 'string', 'max' => 250],
            // [['image'], 'file', 'extensions' => 'png, jpg'],
            [['image'], 'required', 'on' => self::SCENARIO_CREATE],
            //[['image'], 'required', 'on' => self::SCENARIO_CREATE_API],
            [['image'], 'required', 'when' => function ($model) {
                //return $model->is_brand_image_empty == '1';
                return $model->scenario == self::SCENARIO_CREATE;
            }, 'whenClient' => "function (attribute, value) {
                    if ($('#brand-is_brand_image_empty').val() == 1) {            
                                    return $('#brand-image').val() == '';                                    
                                    }
            }",],
            //[['image'], 'file', 'extensions' => 'jpg, png'],
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
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'products' => 'products',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['brand_id' => 'id']);
    }

    /**
     * @param $brandId
     * @return array|ActiveRecord|null
     */
    public function isBrandOfTheWeek($brandId)
    {
        $brandFromDate = date("Y-m-d 00:00:01", strtotime('-1 week'));
        $brandToDate = date("Y-m-d 23:59:59");
        $query1 = Brand::find();

        $query1->innerJoin('products', 'products.brand_id="' . $brandId . '"');
        $query1->leftjoin('order_items', 'order_items.product_id=products.id');
        $query1->rightjoin('orders', 'orders.id=order_items.order_id');
        $query1->select(['SUM(order_items.quantity) AS total_sold_product', 'products.brand_id']);
        $query1->where(['products.brand_id' => $brandId]);
        $subQuery = $query1->where(['between', 'order_items.created_at', $brandFromDate, $brandToDate])->andWhere(['orders.status' => Order::STATUS_ORDER_COMPLETED])->asArray()->one();
        return $subQuery;
    }
}
