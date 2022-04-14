<?php

namespace app\models\search;

use app\models\ProductStatus;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Product;
use Yii;

/**
 * ProductsSearch represents the model behind the search form of `app\modules\admin\models\Products`.
 */
class ProductSearch extends Product
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'number', 'category_id', 'sub_category_id', 'available_quantity', 'brand_id', 'height', 'weight', 'width', 'is_admin_favourite'], 'integer'],
            [['type', 'name', 'option_size', 'option_conditions', 'option_show_only', 'description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned', 'receipt', 'created_at', 'updated_at', 'status_id'], 'safe'],
            [['price', 'option_price'], 'number'],
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
        $query = Product::find()->where(['not in', 'status_id', [ProductStatus::STATUS_ARCHIVED]]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC, 'updated_at' => SORT_DESC]],
            'pagination' => [
                'pageSize' => (!empty(\Yii::$app->params['default_page_size_for_backend'])) ? \Yii::$app->params['default_page_size_for_backend'] : 50,
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

        if (Yii::$app->controller->action->id == "new-product") {

            $query->andWhere(['status_id' => 1]);

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
            'number' => $this->number,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'brand_id' => $this->brand_id,
            'height' => $this->height,
            'weight' => $this->weight,
            'width' => $this->width,
            'is_admin_favourite' => $this->is_admin_favourite,
            'type' => $this->type,
            'updated_at' => $this->updated_at,
            'status_id' => $this->status_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'option_size', $this->option_size])
            ->andFilterWhere(['like', 'option_conditions', $this->option_conditions])
            ->andFilterWhere(['like', 'option_show_only', $this->option_show_only])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'is_top_selling', $this->is_top_selling])
            ->andFilterWhere(['like', 'is_top_trending', $this->is_top_trending])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'price', $this->price])
            ->andFilterWhere(['like', 'option_price', $this->option_price])
            ->andFilterWhere(['like', 'available_quantity', $this->available_quantity])
            ->andFilterWhere(['like', 'is_cleaned', $this->is_cleaned]);

        return $dataProvider;
    }

}
