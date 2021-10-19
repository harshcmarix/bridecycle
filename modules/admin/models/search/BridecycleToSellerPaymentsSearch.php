<?php

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\BridecycleToSellerPayments;

/**
 * BridecycleToSellerPaymentsSearch represents the model behind the search form of `app\models\BridecycleToSellerPayments`.
 */
class BridecycleToSellerPaymentsSearch extends BridecycleToSellerPayments
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'status'], 'integer'],
            [['amount'], 'number'],
            [['order_item_id', 'seller_id', 'note_content', 'created_at', 'updated_at'], 'safe'],
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
        $query = BridecycleToSellerPayments::find();

        // add conditions that should always apply here

        $query->leftJoin('users As seller', 'bridecycle_to_seller_payments.seller_id=seller.id');
        $query->leftJoin('order_items', 'bridecycle_to_seller_payments.order_item_id=order_items.id');
        $query->leftJoin('products', 'order_items.product_id=products.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
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
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'bridecycle_to_seller_payments.order_id', $this->order_id])
            ->andFilterWhere(['or',
                ['like', 'seller.first_name', $this->seller_id],
                ['like', 'seller.last_name', $this->seller_id]
            ])
            ->andFilterWhere(['like', 'products.name', $this->order_item_id])
            ->andFilterWhere(['like', 'note_content', $this->note_content]);

        return $dataProvider;
    }
}
