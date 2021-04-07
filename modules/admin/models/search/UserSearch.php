<?php

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\User;

/**
 * Class UserSearch
 * @package app\modules\admin\models\search
 */
class UserSearch extends User
{
    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            [['id', 'mobile', 'shop_phone_number'], 'integer'],
            [['profile_picture', 'first_name', 'last_name', 'email', 'password_hash', 'temporary_password', 'access_token', 'access_token_expired_at', 'password_reset_token', 'personal_information', 'user_type', 'is_shop_owner', 'shop_name', 'shop_email', 'created_at', 'updated_at'], 'safe'],
            [['weight', 'height'], 'number'],
        ];
    }

    /**
     * @return array|array[]
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'access_token_expired_at' => $this->access_token_expired_at,
            'mobile' => $this->mobile,
            'weight' => $this->weight,
            'height' => $this->height,
            'shop_phone_number' => $this->shop_phone_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'profile_picture', $this->profile_picture])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'temporary_password', $this->temporary_password])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'personal_information', $this->personal_information])
            ->andFilterWhere(['like', 'user_type', $this->user_type])
            ->andFilterWhere(['like', 'is_shop_owner', $this->is_shop_owner])
            ->andFilterWhere(['like', 'shop_name', $this->shop_name])
            ->andFilterWhere(['like', 'shop_email', $this->shop_email]);

        return $dataProvider;
    }
}