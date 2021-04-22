<?php

namespace app\modules\api\v1\models\search;

use app\models\ProductImage;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\Product;

/**
 * ProductSearch represents the model behind the search form of `app\models\Product`.
 */
class ProductSearch extends Product
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
            [['id', 'user_id', 'category_id', 'sub_category_id', 'price', 'available_quantity', 'brand_id', 'height', 'weight', 'width', 'status_id'], 'integer'],
            [['name', 'number', 'option_size', 'option_conditions', 'option_show_only', 'description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned', 'receipt', 'created_at', 'updated_at'], 'safe'],
            [['option_price'], 'number'],
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
    /**
     * @param $requestParams
     * @return ActiveDataProvider
     */
    public function search($requestParams)
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
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['products.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        /* ########## Prepare Query With Default Filter End ######### */


        /* ########## Prepare Query With custom Filter Start ######### */

        if (!empty($requestParams['is_top_selling']) && $requestParams['is_top_selling'] == '1') {
            $query->andWhere(['is_top_selling' => Product::IS_TOP_SELLING_YES]);
        }

        if (!empty($requestParams['user_id'])) {
            $query->andWhere(['user_id' => $requestParams['user_id']]);
        }

        if (!empty($requestParams['is_top_trending']) && $requestParams['is_top_trending'] == '1') {
            $query->andWhere(['is_top_trending' => Product::IS_TOP_TRENDING_YES]);
        }

        if (!empty($requestParams['new_arrival']) && $requestParams['new_arrival'] == '1') {
            $brandFromDate = date("Y-m-d 00:00:01", strtotime('-' . Yii::$app->params['recent_time']));
            $brandToDate = date("Y-m-d 23:59:59");
            $query->andWhere(['between', 'products.created_at', $brandFromDate, $brandToDate]);
        }

        /* ########## Prepare Query With custom Filter End ######### */

        $query->groupBy('products.id');

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
        $productModelData = $activeDataProvider->getModels();

        $productData = [];
        foreach ($productModelData as $key => $value) {
            $productImg = [];
            if (!empty($value->productImages)) {
                foreach ($value->productImages as $keys => $productImageRow) {
                    if (!empty($productImageRow) && $productImageRow instanceof ProductImage && !empty($productImageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $productImageRow->name)) {

                        $productImg[] = Yii::$app->request->getHostInfo() . Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $productImageRow->name;
                    }
                }
            }

            $data['status'] = (!empty($productModelData[$key]['status_id']) && !empty($value->status) && !empty($value->status->status)) ? $value->status->status : "";
            $data['user'] = (!empty($productModelData[$key]['user_id']) && !empty($value->user)) ? $value->user->first_name . " " . $value->user->last_name : "";
            $data['brand'] = (!empty($productModelData[$key]['brand_id']) && !empty($value->brand) && !empty($value->brand->name)) ? $value->brand->name : "";
            $data['image'] = $productImg;

            $productData[] = array_merge($value->toArray(), $data);
        }
        $productModelData = $productData;
        $activeDataProvider->setModels($productModelData);
        return $activeDataProvider;
    }
}
