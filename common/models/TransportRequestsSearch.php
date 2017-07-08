<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportRequests;

/**
 * TransportRequestsSearch represents the model behind the search form about `common\models\TransportRequests`.
 */
class TransportRequestsSearch extends TransportRequests
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'finished_at', 'customer_id', 'region_id', 'city_id', 'state_id', 'our_loading', 'periodicity_id', 'spec_free'], 'integer'],
            [['customer_name', 'address', 'comment_manager', 'comment_logist', 'special_conditions', 'spec_hose', 'spec_cond'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = TransportRequests::find();

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
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'finished_at' => $this->finished_at,
            'customer_id' => $this->customer_id,
            'region_id' => $this->region_id,
            'city_id' => $this->city_id,
            'state_id' => $this->state_id,
            'our_loading' => $this->our_loading,
            'periodicity_id' => $this->periodicity_id,
            'spec_free' => $this->spec_free,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'comment_manager', $this->comment_manager])
            ->andFilterWhere(['like', 'comment_logist', $this->comment_logist])
            ->andFilterWhere(['like', 'special_conditions', $this->special_conditions])
            ->andFilterWhere(['like', 'spec_hose', $this->spec_hose])
            ->andFilterWhere(['like', 'spec_cond', $this->spec_cond]);

        return $dataProvider;
    }
}
