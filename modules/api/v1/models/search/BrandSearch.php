<?php

namespace app\modules\api\v1\models\search;

use app\models\Order;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\Brand;

/**
 * BrandSearch represents the model behind the search form of `app\models\Brand`.
 */
class BrandSearch extends Brand
{
    /**
     * @var $hiddenFields Array of hidden fields which not needed in APIs
     */
    protected $hiddenFields = [];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'image', 'is_top_brand', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($requestParams)
    {
//p($requestParams);
        /* ########## Prepare Request Filter Start ######### */
        if (!empty($requestParams['filter'])) {
            foreach ($requestParams['filter'] as $key => $val) {
                if ($key === 'dropdown') {
                    if (is_array($val) && !empty($val)) {
                        foreach ($val as $k => $v) {
                            if (!empty($v['like'])) {
                                $requestParams['filter'][$key][$k]['like'] = trim(urldecode($v['like']));
                            } else {
                                if (isset($v) && !is_array($v) && $v != '') {
                                    $requestParams['filter'][$key][$k] = trim(urldecode($v));
                                }
                            }
                        }
                    }
                } else {
                    if (!empty($val['like'])) {
                        $requestParams['filter'][$key]['like'] = trim(urldecode($val['like']));
                    } else {
                        if (isset($val) && !is_array($val) && $val != '') {
                            $requestParams['filter'][$key] = trim(urldecode($val));
                        }
                    }
                }
            }
        }
        /* ########## Prepare Request Filter End ######### */

        /* ########## Active Data Filter Start ######### */
        $activeDataFilter = new ActiveDataFilter();
        $activeDataFilter->setSearchModel($this);
        $filter = null;
        if (isset($requestParams['filter']['dropdown'])) {
            unset($requestParams['filter']['dropdown']);
        }

        if ($activeDataFilter !== null) {
            if ($activeDataFilter->load($requestParams)) {
                $filter = $activeDataFilter->build();
                if ($filter === false) {
                    return $activeDataFilter;
                }
            }
        }
        /* ########## Active Data Filter End ######### */

        /* ########## Prepare Query With Default Filter Start ######### */
        $query = self::find();

        if (!empty($requestParams) && $requestParams['brand_of_the_week'] == '1') {
            $brandFromDate = date("Y-m-d 00:00:01", strtotime('-1 week'));
            $brandToDate = date("Y-m-d 23:59:59");

            $query->innerJoin('products', 'products.brand_id=brands.id');
            $query->leftjoin('order_items', 'order_items.product_id=products.id');
            $query->rightjoin('orders', 'orders.id=order_items.order_id');

            $query->where(['between', 'order_items.created_at', $brandFromDate, $brandToDate])->andWhere(['orders.status' => Order::STATUS_ORDER_COMPLETED]);
        }


        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else if (!empty($requestParams) && $requestParams['brand_of_the_week'] == '1') {
            $select = ['brands.*', 'sum(order_items.quantity) As total_sold_product'];
        } else {
            $select = ['brands.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        if (!empty($requestParams) && $requestParams['brand_of_the_week'] == '1') {
            $query->groupBy('products.id')->orderBy(['total_sold_product' => SORT_DESC]);
        } else {
            $query->groupBy('brands.id');
        }

        $activeDataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
                'pageSize' => isset($requestParams['pageSize']) ? $requestParams['pageSize'] : Yii::$app->params['default_page_size'], //set page size here
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);

        $brandModelData = $activeDataProvider->getModels();

        foreach ($brandModelData as $key => $value) {
            $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($brandModelData[$key]['image']) && file_exists(Yii::getAlias('@brandImageThumbRelativePath') . '/' . $value->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $value->image;
            }
            $brandModelData[$key]['image'] = $brandImage;
        }
        $activeDataProvider->setModels($brandModelData);
        return $activeDataProvider;
    }
}
