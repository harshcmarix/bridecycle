<?php

namespace app\modules\api\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\api\models\User;

/**
 * Class UserSearch
 * @package app\modules\api\models\search
 */
class UserSearch extends User
{
    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            [['id', 'mobile'], 'integer'],
            [['first_name', 'last_name', 'email', 'password_hash', 'access_token', 'access_token_expired_at', 'user_type', 'is_shop_owner', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @return array|array[]
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
        $query = User::find();

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
            'access_token_expired_at' => $this->access_token_expired_at,
            'mobile' => $this->mobile,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'user_type', $this->user_type])
            ->andFilterWhere(['like', 'is_shop_owner', $this->is_shop_owner]);

        return $dataProvider;
    }
}