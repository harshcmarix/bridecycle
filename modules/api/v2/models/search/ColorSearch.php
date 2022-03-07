<?php

namespace app\modules\api\v2\models\search;

use app\models\Product;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\Color;

/**
 * ColorSearch represents the model behind the search form of `app\models\Color`.
 */
class ColorSearch extends Color
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
            [['name', 'code', 'created_at', 'updated_at'], 'safe'],
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
    public function search($requestParams, $from = null, $product_id = null)
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
        $query = self::find()->where(['status' => Color::STATUS_APPROVE]);

        // Edit product Pending Approve color get start
        if (!empty($from) && $from == "edit_product" && !empty($product_id)) {
            $modelProduct = Product::findOne($product_id);
            if (!empty($modelProduct) && $modelProduct instanceof Product && !empty($modelProduct->option_color)) {
                $colorIds = explode(",", $modelProduct->option_color);
                if (!empty($colorIds)) {
                    $query->orWhere(['in', 'color.id', $colorIds]);
                }
            }
        }
        // Edit product Pending Approve color get end

        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['color.*',];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        $query->groupBy('color.id');

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

        $colorModelData = $activeDataProvider->getModels();
        if (!empty($colorModelData)) {
            foreach ($colorModelData as $key => $data) {
                if (!empty($data) && $data instanceof Color) {
                    //$colorModelData[$key]['name'] = (!empty($data->name)) ? $data->name : "";

                    $colorName = "";
                    if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'english') {
                        if (!empty($data->name)) {
                            $colorName = $data->name;
                        } elseif (empty($data->name) && !empty($data->german_name)) {
                            $colorName = $data->german_name;
                        }
                    }

                    if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'german') {
                        if (!empty($data->german_name)) {
                            $colorName = $data->german_name;
                        } elseif (empty($data->german_name) && !empty($data->name)) {
                            $colorName = $data->name;
                        }
                    }
                    $colorModelData[$key]['name'] = $colorName;
                    $colorModelData[$key]['code'] = (!empty($data->code)) ? $data->code : "";
                }
            }
        }
        $activeDataProvider->setModels($colorModelData);
        return $activeDataProvider;
    }
}
