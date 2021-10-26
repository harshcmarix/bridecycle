<?php

namespace app\modules\api\v2\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\MakeOffer;

/**
 * MakeOfferSearch represents the model behind the search form of `app\models\MakeOffer`.
 */
class MakeOfferSearch extends MakeOffer
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
            [['id', 'product_id', 'sender_id', 'receiver_id', 'status'], 'integer'],
            [['offer_amount'], 'number'],
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
     * It is used for buyer
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
            $query->where(['sender_id' => $userId]);
        }
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['make_offer.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        /* ########## Prepare Query With Custom Filter Start ######### */
        if (!empty($requestParams['product_id'])) {
            $query->andWhere(['product_id' => $requestParams['product_id']]);
        }
        /* ########## Prepare Query With Custom Filter End ######### */

        $query->orderBy(['make_offer.created_at' => SORT_DESC]);
        if (empty($requestParams['product_id'])) {
            $query->groupBy(['make_offer.product_id', 'make_offer.id']);
        } else {
            $query->groupBy('make_offer.id');
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
                //'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $makeOfferModelData = $activeDataProvider->getModels();
        $activeDataProvider->setModels($makeOfferModelData);
        return $activeDataProvider;
    }

    /**
     * @param $requestParams
     * @param null $userId
     * @return \#M#Ф\app\modules\api\v2\models\search\MakeOfferSearch.find[]|\#o#Э#A#M#C\app\modules\api\v2\models\search\MakeOfferSearch.search.0[][]|\[][]|object|ActiveDataFilter|ActiveDataProvider[]
     * @throws \yii\base\InvalidConfigException
     *
     * It is used for seller
     */
    public function searchSeller($requestParams, $userId = null)
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
            $query->where(['receiver_id' => $userId]);
        }
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['make_offer.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        /* ########## Prepare Query With Custom Filter Start ######### */
        if (!empty($requestParams['product_id'])) {
            $query->andWhere(['product_id' => $requestParams['product_id']]);
        }
        /* ########## Prepare Query With Custom Filter End ######### */

        $query->orderBy(['make_offer.created_at' => SORT_DESC]);
        if (empty($requestParams['product_id'])) {
            $query->groupBy(['make_offer.product_id', 'make_offer.id']);
        } else {
            $query->groupBy('make_offer.id');
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
                //'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $makeOfferModelData = $activeDataProvider->getModels();
        $activeDataProvider->setModels($makeOfferModelData);
        return $activeDataProvider;
    }
}
