<?php

namespace app\models\search;

use app\models\Product;
use yii\base\Model;
use yii\data\ActiveDataProvider;


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
            [['id', 'number', 'category_id', 'sub_category_id', 'price', 'available_quantity', 'brand_id', 'height', 'weight', 'width'], 'integer'],
            [['name', 'option_size', 'option_conditions', 'option_show_only', 'description', 'is_top_selling', 'is_top_trending', 'gender', 'is_cleaned', 'receipt', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params)
    {
        $query = Product::find();

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
            'number' => $this->number,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'price' => $this->price,
            'option_price' => $this->option_price,
            'available_quantity' => $this->available_quantity,
            'brand_id' => $this->brand_id,
            'height' => $this->height,
            'weight' => $this->weight,
            'width' => $this->width,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'option_size', $this->option_size])
            ->andFilterWhere(['like', 'option_conditions', $this->option_conditions])
            ->andFilterWhere(['like', 'option_show_only', $this->option_show_only])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'is_top_selling', $this->is_top_selling])
            ->andFilterWhere(['like', 'is_top_trending', $this->is_top_trending])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'is_cleaned', $this->is_cleaned])
            ->andFilterWhere(['like', 'receipt', $this->receipt]);

        return $dataProvider;
    }
}
