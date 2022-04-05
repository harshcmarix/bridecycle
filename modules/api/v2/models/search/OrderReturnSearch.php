<?php

namespace app\modules\api\v2\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OrderReturn;
use yii\data\ActiveDataFilter;
use Yii;

/**
 * OrderReturnSearch represents the model behind the search form of `app\models\OrderReturn`.
 */
class OrderReturnSearch extends OrderReturn
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
            [['id', 'order_id', 'seller_id', 'buyer_id', 'is_other_reason', 'status'], 'integer'],
            [['reason', 'description', 'image_one', 'image_two', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params)
    {
        $query = OrderReturn::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'order_id' => $this->order_id,
            'seller_id' => $this->seller_id,
            'buyer_id' => $this->buyer_id,
            'is_other_reason' => $this->is_other_reason,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'image_one', $this->image_one])
            ->andFilterWhere(['like', 'image_two', $this->image_two]);

        return $dataProvider;
    }

    /**
     * @param $requestParams
     * @param null $userId
     * @return mixed
     */
    public function searchBuyer($requestParams, $userId = null)
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
            $query->where(['buyer_id' => $userId]);
        }

        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['order_return.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        if (!empty($requestParams['user_id'])) {
            $query->andWhere(['buyer_id' => $requestParams['user_id']]);
        }

        /* ########## Prepare Query With Default Filter End ######### */

        $query->orderBy(['order_return.created_at' => SORT_DESC]);
        $query->groupBy('order_return.id');

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

        $orderReturnModels = $activeDataProvider->getModels();


        foreach ($orderReturnModels as $key => $value) {
            if (!empty($value) && $value instanceof OrderReturn) {
                if (!empty($value->image_one)) {
                    if (file_exists(Yii::getAlias('@orderReturnImageRelativePath') . "/" . $value->image_one)) {
                        $value->image_one = Yii::$app->request->getHostInfo() . Yii::getAlias('@orderReturnImageAbsolutePath') . '/' . $value->image_one;
                    }
                }

                if (!empty($value->image_two)) {
                    if (file_exists(Yii::getAlias('@orderReturnImageRelativePath') . "/" . $value->image_two)) {
                        $value->image_two = Yii::$app->request->getHostInfo() . Yii::getAlias('@orderReturnImageAbsolutePath') . '/' . $value->image_two;
                    }
                }
            }
        }


        $activeDataProvider->setModels($orderReturnModels);

        return $activeDataProvider;
    }

    /**
     * @param $requestParams
     * @param null $userId
     * @return mixed
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
            $query->where(['seller_id' => $userId]);
        }

        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['order_return.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        if (!empty($requestParams['user_id'])) {
            $query->andWhere(['seller_id' => $requestParams['user_id']]);
        }

        /* ########## Prepare Query With Default Filter End ######### */

        $query->orderBy(['order_return.created_at' => SORT_DESC]);
        $query->groupBy('order_return.id');

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

        $orderReturnModels = $activeDataProvider->getModels();

        foreach ($orderReturnModels as $key => $value) {
            if (!empty($value) && $value instanceof OrderReturn) {
                if (!empty($value->image_one)) {
                    if (file_exists(Yii::getAlias('@orderReturnImageRelativePath') . "/" . $value->image_one)) {
                        $value->image_one = Yii::$app->request->getHostInfo() . Yii::getAlias('@orderReturnImageAbsolutePath') . '/' . $value->image_one;
                    }
                }

                if (!empty($value->image_two)) {
                    if (file_exists(Yii::getAlias('@orderReturnImageRelativePath') . "/" . $value->image_two)) {
                        $value->image_two = Yii::$app->request->getHostInfo() . Yii::getAlias('@orderReturnImageAbsolutePath') . '/' . $value->image_two;
                    }
                }
            }
        }

        $activeDataProvider->setModels($orderReturnModels);

        return $activeDataProvider;
    }
}
