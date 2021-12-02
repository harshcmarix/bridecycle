<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Order;

/**
 * OrderSearch represents the model behind the search form of `app\models\Order`.
 */
class OrderSearch extends Order
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'user_address_id'], 'integer'],
            [['status', 'created_at', 'updated_at'], 'safe'],
            [['total_amount'], 'number'],
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
        $query = Order::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                //'pageSize' => (!empty(\Yii::$app->params['default_page_size_for_backend'])) ? \Yii::$app->params['default_page_size_for_backend'] : 50,
                'pageSize' => 50,
            ]
        ]);

        $this->load($params);

        if (!empty($this->created_at)) {
            $dateWiseFilter = $this->created_at;
            $dates = explode(" to ", $dateWiseFilter);
            $startDate = date('Y-m-d 00:00:01', strtotime($dates[0]));
            $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));

            $query->andWhere(['between', 'created_at', $startDate, $endDate]);
        }
        
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_address_id' => $this->user_address_id,
            //'total_amount' => $this->total_amount,
            //'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'total_amount', $this->total_amount . "%", false])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
