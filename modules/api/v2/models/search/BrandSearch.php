<?php

namespace app\modules\api\v2\models\search;

use app\models\Brand;
use app\models\Order;
use app\models\Product;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;

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
    public function search($requestParams, $from = null, $product_id = null, $userId = null)
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
        $query = self::find()->where(['IN', 'brands.status', [Brand::STATUS_APPROVE]]);

        // Brand of the week start
        if (!empty($requestParams['brand_of_the_week']) && $requestParams['brand_of_the_week'] == '1') {
            $brandFromDate = date("Y-m-d 00:00:01", strtotime('-1 week'));
            $brandToDate = date("Y-m-d 23:59:59");

            $query->innerJoin('products', 'products.brand_id=brands.id');
            $query->leftjoin('order_items', 'order_items.product_id=products.id');
            $query->rightjoin('orders', 'orders.id=order_items.order_id');

            $query->andWhere(['between', 'order_items.created_at', $brandFromDate, $brandToDate])->andWhere(['orders.status' => Order::STATUS_ORDER_DELIVERED]);
        }
        // Brand of the week end


        // Edit product Pending Approve color get start
        if (!empty($from) && $from == "edit_product" && !empty($product_id)) {
            $modelProduct = Product::findOne($product_id);
            if (!empty($modelProduct) && $modelProduct instanceof Product && !empty($modelProduct->brand_id)) {
                $brandIds = explode(",", $modelProduct->brand_id);

                if (!empty($brandIds)) {
                    $query->orWhere(['in', 'brands.id', $brandIds]);
                }
            }
        }
        // Edit product Pending Approve color get end


        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else if (!empty($requestParams) && !empty($requestParams['brand_of_the_week']) && $requestParams['brand_of_the_week'] == '1') {
            $select = ['brands.*', 'sum(order_items.quantity) As total_sold_product'];
        } else {
            $select = ['brands.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        if (!empty($requestParams) && !empty($requestParams['brand_of_the_week']) && $requestParams['brand_of_the_week'] == '1') {
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
            if (!empty($brandModelData[$key]['image']) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $value->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageAbsolutePath') . '/' . $value->image;
            }

            $brandName = "";
            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'en' || \Yii::$app->language == 'english') {
                if (!empty($value->name)) {
                    $brandName = $value->name;
                } elseif (empty($value->name) && !empty($value->german_name)) {
                    $brandName = $value->german_name;
                }
            }

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'de' || \Yii::$app->language == 'german') {
                if (!empty($value->german_name)) {
                    $brandName = $value->german_name;
                } elseif (empty($value->german_name) && !empty($value->name)) {
                    $brandName = $value->name;
                }
            }
            $brandModelData[$key]['name'] = $brandName;
            $brandModelData[$key]['image'] = $brandImage;
        }
        $activeDataProvider->setModels($brandModelData);
        return $activeDataProvider;
    }
}
