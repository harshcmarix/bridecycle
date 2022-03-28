<?php

namespace app\modules\api\v2\models\search;

use app\models\Product;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OrderItem;
use yii\data\ActiveDataFilter;
use Yii;

/**
 * OrderItemSearch represents the model behind the search form of `app\models\OrderItem`.
 */
class OrderItemSearch extends OrderItem
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
            [['id', 'order_id', 'product_id', 'seller_id', 'quantity', 'size_id'], 'integer'],
            [['product_name', 'category_name', 'subcategory_name', 'color', 'size', 'order_tracking_id', 'invoice', 'created_at', 'updated_at'], 'safe'],
            [['price', 'tax', 'shipping_cost'], 'number'],
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
    public function search($requestParams, $userId = null)
    {

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

        if (!empty($userId)) {
            $productIds = Product::find()->select('id')->where(['user_id' => $userId])->column();
            $query->where(['in', 'product_id', $productIds]);
        }

        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['order_items.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

//        if (!empty($requestParams['user_id'])) {
//            $query->andWhere(['user_id' => $requestParams['user_id']]);
//        }

        /* ########## Prepare Query With Default Filter End ######### */

        $query->orderBy(['order_items.created_at' => SORT_DESC]);
        $query->groupBy('order_items.id');

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

        $orderItemModels = $activeDataProvider->getModels();
        $activeDataProvider->setModels($orderItemModels);
        /**
         * get and set order status name
         */

        //$getStatusArray = new Order();
//        $arrOrderStatus = $getStatusArray->arrOrderStatus;
//
//        foreach ($orderModels as $key => $value) {
//            if (!empty($value->status) && array_key_exists($value->status, $arrOrderStatus)) {
//                $value->status = $arrOrderStatus[$value->status];
//            }
//        }

        return $activeDataProvider;
    }
}
