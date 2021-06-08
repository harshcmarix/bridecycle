<?php

namespace app\modules\api\v1\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Notification;

/**
 * NotificationSearch represents the model behind the search form of `app\models\Notification`.
 */
class NotificationSearch extends Notification
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'notification_receiver_id', 'ref_id'], 'integer'],
            [['notification_text', 'action', 'ref_type', 'created_at'], 'safe'],
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
        $query = Notification::find();

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
            'owner_id' => $this->owner_id,
            'notification_receiver_id' => $this->notification_receiver_id,
            'ref_id' => $this->ref_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'notification_text', $this->notification_text])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'ref_type', $this->ref_type]);

        return $dataProvider;
    }
}
