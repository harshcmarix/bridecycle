<?php

namespace app\modules\api\v2\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\RecentSearchProducts;

/**
 * RecentSearchProductsSearch represents the model behind the search form of `app\models\RecentSearchProducts`.
 */
class RecentSearchProductsSearch extends RecentSearchProducts
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
            [['id', 'user_id', 'product_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            $query->where(['recent_search_products.user_id' => $userId]);
        }
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['recent_search_products.*',];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        $query->orderBy(['recent_search_products.id' => SORT_DESC]);
        $query->groupBy('recent_search_products.product_id');
        $query->limit(Yii::$app->params['recent_search_product_list_default_limit']);

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
        $modelData = $activeDataProvider->getModels();

        if (!empty($modelData)) {
            foreach ($modelData as $key => $modelDataRow) {
                if (!empty($modelDataRow)) {
                    if (!empty($modelDataRow) && $modelDataRow instanceof RecentSearchProducts) {
                        $data['price'] = (!empty($modelDataRow->product) && !empty($modelDataRow->product->price)) ? (double)$modelDataRow->product->price : null;

                        $data['product'] = (!empty($modelDataRow->product)) ? $modelDataRow->product : null;
                        $dataProduct['productImages0'] = (!empty($modelDataRow->product) && !empty($modelDataRow->product->productImages0)) ? $modelDataRow->product->productImages0 : [];
                        $dataProduct['category0'] = (!empty($modelDataRow->product) && !empty($modelDataRow->product->category0)) ? $modelDataRow->product->category0 : "";
                        $dataProduct['brand0'] = (!empty($modelDataRow->product) && !empty($modelDataRow->product->brand0)) ? $modelDataRow->product->brand0 : "";
                        $dataProduct['favouriteProduct'] = (!empty($modelDataRow->product) && !empty($modelDataRow->product->favouriteProducts)) ? $modelDataRow->product->favouriteProducts : [];

                        $preResult = array_merge($data['product']->toArray(), $dataProduct);
                        $result = array_merge($modelData[$key]->toArray(), $preResult);
                        $modelData[$key] = $result;
                    }
                }
            }
        }

        $activeDataProvider->setModels($modelData);
        return $activeDataProvider;
    }
}
