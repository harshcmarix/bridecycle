<?php

namespace app\modules\api\v2\models\search;

use app\models\Product;
use app\models\ProductImage;
use app\models\ProductStatus;
use app\models\SearchHistory;
use app\modules\api\v2\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;

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
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'productImages0' => 'productImages0',
            'category0' => 'category0',
            'brand0' => 'brand0',
            'color' => 'color',
            'user0' => 'user0',
            'subCategory0' => 'subCategory0',
            'status' => 'status',
            'address' => 'address',
            'favouriteProduct' => 'favouriteProduct',
            'shippingCountry0' => 'shippingCountry0',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'category_id', 'sub_category_id', 'price', 'available_quantity', 'brand_id', 'option_color', 'height', 'weight', 'width', 'status_id'], 'integer'],
            [['name', 'number', 'option_size', 'option_conditions', 'option_show_only', 'description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned', 'is_receipt', 'receipt', 'created_at', 'updated_at'], 'safe'],
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
        if (empty($requestParams['user_id']) && empty($requestParams['is_from_sell_screen']) || $requestParams['is_from_sell_screen'] == 0) {
            $query->where(['IN', 'products.status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]]);
        }


        if (!empty($requestParams['is_from_search_screen']) && $requestParams['is_from_search_screen'] == 1) {
            $query->joinWith('category AS category');
            $query->joinWith('subCategory AS subCategory');
            $query->joinWith('brand AS brand');
        }

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

//        if (!empty(Yii::$app->user->identity) && !empty(Yii::$app->user->identity->id)) {
//            $modelUser = Yii::$app->user->identity;
//            if (!empty($modelUser) && $modelUser instanceof User) {
//                $blockUserIds = $modelUser->BlockUsersId();
//                p($blockUserIds);
//            }
//        }

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

        if (!empty($requestParams['name'])) {
            $query->andFilterWhere([
                'and',
                ['like', 'products.name', $requestParams['name']],
            ]);
        }

        if (!empty($requestParams['brand_id'])) {
            $brandIDs = explode(",", $requestParams['brand_id']);
            $query->andFilterWhere([
                'and',
                ['in', 'products.brand_id', $brandIDs],
            ]);
        }

        if (!empty($requestParams['dress_type_id'])) {
            $dressTypeIDs = explode(",", $requestParams['dress_type_id']);
            $query->andFilterWhere([
                'and',
                ['in', 'products.dress_type_id', $dressTypeIDs],
            ]);
        }

        if (!empty($requestParams['category_id'])) {

            $categoryIDs = explode(",", $requestParams['category_id']);
            $query->andFilterWhere([
                'and',
                ['IN', 'products.category_id', $categoryIDs],
            ]);

        }

        if (!empty($requestParams['color'])) {
            $colors = explode(",", $requestParams['color']);
            if (!empty($colors)) {
                foreach ($colors as $keyColor => $colorRow) {
                    $query->andFilterWhere([
                        'or',
                        ['like', 'products.option_color', $colorRow],
                    ]);
                }
            }
        }

        if (!empty($requestParams['size'])) {
            $sizes = explode(",", $requestParams['size']);
            if (!empty($sizes)) {
                foreach ($sizes as $keySize => $sizeRow) {
                    $query->andFilterWhere([
                        'or',
                        ['like', 'products.option_size', strtolower($sizeRow) . "%", false],
                    ]);
                }
            }
        }

        if (!empty($requestParams['price'])) {
            $prices = explode("-", $requestParams['price']);
            if (!empty($prices)) {
                $query->andFilterWhere([
                    'or',
                    // ['between', 'products.option_price', $prices[0], $prices[1]],
                    ['between', 'products.price', $prices[0], $prices[1]],
                ]);
            }
        }

        if (!empty($requestParams['conditions'])) {
            $conditions = explode(",", $requestParams['conditions']);
            if (!empty($conditions)) {
                foreach ($conditions as $keyCondition => $conditionRow) {
                    $query->orFilterWhere([
                        'or',
                        ['like', 'products.option_conditions', $conditionRow],
                    ]);
                }
            }
        }

        if (!empty($requestParams['show_only'])) {
            $showonlies = explode(",", $requestParams['show_only']);
            if (!empty($showonlies)) {
                foreach ($showonlies as $keyShowonly => $showonlyRow) {
                    $query->orFilterWhere([
                        'or',
                        ['like', 'products.option_show_only', $showonlyRow],
                    ]);
                }
            }
        }

        if (!empty($requestParams['gender']) && strtolower($requestParams['gender']) == 'f') {
            $query->andWhere(['products.gender' => Product::GENDER_FOR_FEMALE]);
        }

        if (!empty($requestParams['id'])) {
            $query->andWhere(['!=', 'products.id', $requestParams['id']]);
        }

        if (!empty($requestParams['is_bridecycle_favourite']) && $requestParams['is_bridecycle_favourite'] == Product::IS_ADMIN_FAVOURITE_YES) {
            $query->andWhere(['products.is_admin_favourite' => Product::IS_ADMIN_FAVOURITE_YES]);
        }

        /** Start for search screen */

        if (!empty($requestParams['is_from_search_screen']) && $requestParams['is_from_search_screen'] == 1 && !empty($requestParams['search_keyword'])) {
            $query->orFilterWhere([
                'or',
                ['like', 'products.name', $requestParams['search_keyword']],
                ['like', 'category.name', $requestParams['search_keyword']],
                ['like', 'subCategory.name', $requestParams['search_keyword']],
                ['like', 'brand.name', $requestParams['search_keyword']],
            ]);
        }

        if (!empty($requestParams['is_from_search_screen']) && $requestParams['is_from_search_screen'] == 1 && empty($requestParams['search_keyword'])) {
            $data = SearchHistory::find()->where(['user_id' => Yii::$app->user->identity->id])->orderBy(['id' => SORT_DESC])->all();
            if (!empty($data)) {
                foreach ($data as $dataRow) {
                    if (!empty($dataRow) && $dataRow instanceof SearchHistory) {
                        $query->orFilterWhere([
                            'or',
                            ['like', 'products.name', $dataRow->search_text],
                            ['like', 'category.name', $dataRow->search_text],
                            ['like', 'subCategory.name', $dataRow->search_text],
                            ['like', 'brand.name', $dataRow->search_text],
                        ]);
                    }
                }
            }
        }

        // For sale product listing screen
        if (!empty($requestParams['user_id']) && !empty($requestParams['is_from_sell_screen']) && $requestParams['is_from_sell_screen'] == 1) {
            $query->andWhere(['user_id' => $requestParams['user_id']]);
            $query->andWhere(['in', 'products.status_id', [ProductStatus::STATUS_PENDING_APPROVAL, ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK, ProductStatus::STATUS_SOLD]]);
        }

        /** End for search screen */

        /** Start for Block user */
        if (!empty($userId)) {
            $modelUser = User::find()->where(['id' => $userId])->one();
            $query->andWhere(['NOT IN', 'user_id', $modelUser->blockUsersId]);

        }
        /** End for Block user */

        if (!empty($requestParams['sort_by'])) {
            if (strtolower($requestParams['sort_by']) == 'nf') {
                $query->orderBy(['products.created_at' => SORT_DESC]);
            } elseif (strtolower($requestParams['sort_by']) == 'of') {
                $query->orderBy(['products.created_at' => SORT_ASC]);
            } elseif (strtolower($requestParams['sort_by']) == 'phl') {
                $query->orderBy(['products.price' => SORT_DESC, 'products.option_price' => SORT_DESC]);
            } elseif (strtolower($requestParams['sort_by']) == 'plh') {
                $query->orderBy(['products.price' => SORT_ASC, 'products.option_price' => SORT_ASC]);
            }
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
        foreach ($productModelData as $key => $value) {
            $productImg = [];
            if (!empty($value->productImages)) {
                foreach ($value->productImages as $keys => $productImageRow) {
                    if (!empty($productImageRow) && $productImageRow instanceof ProductImage && !empty($productImageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $productImageRow->name)) {

                        $productImg[] = Yii::$app->request->getHostInfo() . Yii::getAlias('@productImageAbsolutePath') . '/' . $productImageRow->name;
                    }

                }
            }
        }

        //$productModelData = $productData;
        $activeDataProvider->setModels($productModelData);
        return $activeDataProvider;
    }
}
