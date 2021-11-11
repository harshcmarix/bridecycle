<?php

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\User;
use Yii;

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
            [['id', 'mobile'], 'integer'],
            [['profile_picture', 'first_name', 'last_name', 'email', 'password_hash', 'temporary_password', 'access_token', 'access_token_expired_at', 'password_reset_token', 'personal_information', 'user_type', 'is_shop_owner', 'is_newsletter_subscription', 'created_at', 'updated_at'], 'safe'],
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
        $query = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                //'pageSize' => (!empty(\Yii::$app->params['default_page_size_for_backend'])) ? \Yii::$app->params['default_page_size_for_backend'] : 50,
                'pageSize' => 50,
            ]
        ]);

        $this->load($params);
        $dateWiseFilter = "";
        if (Yii::$app->controller->action->id == 'index-new-customer') {
            $query->andWhere(['is_shop_owner' => '0']);

            if (!empty($this->created_at)) {
                $dateWiseFilter = $this->created_at;

                $dates = explode(" to ", $dateWiseFilter);
                $startDate = date('Y-m-d 00:00:01', strtotime($dates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));
            } else {
                $startDate = date('Y-m-d 00:00:01', strtotime('-35 days'));
                $endDate = date('Y-m-d 23:59:59');
                //$this->created_at = date('d-M-Y', strtotime('-3 days')) . " to " . date('d-M-Y');
            }
            $query->andWhere(['between', 'created_at', $startDate, $endDate]);
        }

        if (Yii::$app->controller->action->id == 'index-new-shop-owner-customer') {
            $query->andWhere(['is_shop_owner' => '1']);

            if (!empty($this->created_at)) {
                $dateWiseFilter = $this->created_at;

                $dates = explode(" to ", $dateWiseFilter);
                $startDate = date('Y-m-d 00:00:01', strtotime($dates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));
            } else {
                $startDate = date('Y-m-d 00:00:01', strtotime('-35 days'));
                $endDate = date('Y-m-d 23:59:59');
                //$this->created_at = date('d-M-Y', strtotime('-3 days')) . " to " . date('d-M-Y');
            }
            $query->andWhere(['between', 'created_at', $startDate, $endDate]);
        }

        if (Yii::$app->controller->action->id == 'index') {
            if (!empty($this->created_at)) {
                $dateWiseFilter = $this->created_at;
                $dates = explode(" to ", $dateWiseFilter);
                $startDate = date('Y-m-d 00:00:01', strtotime($dates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));
                $query->andWhere(['between', 'created_at', $startDate, $endDate]);
            }
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'access_token_expired_at' => $this->access_token_expired_at,
            'weight' => $this->weight,
            'height' => $this->height,
            //'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'profile_picture', $this->profile_picture])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'temporary_password', $this->temporary_password])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'personal_information', $this->personal_information])
            ->andFilterWhere(['like', 'user_type', $this->user_type])
            ->andFilterWhere(['like', 'is_newsletter_subscription', $this->is_newsletter_subscription])
            ->andFilterWhere(['like', 'is_shop_owner', $this->is_shop_owner]);

        return $dataProvider;
    }
}
