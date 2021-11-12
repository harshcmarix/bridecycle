<?php

namespace app\models\search;

use app\models\Order;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Brand;
use Yii;

/**
 * BrandSearch represents the model behind the search form of `app\models\Brand`.
 */
class BrandSearch extends Brand
{

    public $total_sold_product;
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
    public function search($params)
    {
        $query = Brand::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                // 'pageSize' => (!empty(\Yii::$app->params['default_page_size_for_backend'])) ? \Yii::$app->params['default_page_size_for_backend'] : 50,
                'pageSize' => 50,
            ]
        ]);

        $this->load($params);
        $dateWiseFilter = "";
        if (Yii::$app->controller->action->id == 'index') {
            if (!empty($this->created_at)) {
                $dateWiseFilter = $this->created_at;
                $dates = explode(" to ", $dateWiseFilter);
                $startDate = date('Y-m-d 00:00:01', strtotime($dates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));
                $query->andWhere(['between', 'created_at', $startDate, $endDate]);
            }
        }

        if (Yii::$app->controller->action->id == "new-brand") {

            $query->andWhere(['status' => 1]);

            if (!empty($this->created_at)) {
                $dateWiseFilter = $this->created_at;
                $dates = explode(" to ", $dateWiseFilter);
                $startDate = date('Y-m-d 00:00:01', strtotime($dates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));
            } else {
                $startDate = date('Y-m-d 00:00:01', strtotime('-35 days'));
                $endDate = date('Y-m-d 23:59:59');
            }
            $query->andWhere(['between', 'created_at', $startDate, $endDate]);
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            // 'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'is_top_brand', $this->is_top_brand]);

        return $dataProvider;
    }
}
