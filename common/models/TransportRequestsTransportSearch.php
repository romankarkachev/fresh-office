<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportRequestsTransport;

/**
 * TransportRequestsTransportSearch represents the model behind the search form about `common\models\TransportRequestsTransport`.
 */
class TransportRequestsTransportSearch extends TransportRequestsTransport
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'tr_id', 'tt_id'], 'integer'],
            [['amount'], 'number'],
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
        $query = TransportRequestsTransport::find();

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
            'tr_id' => $this->tr_id,
            'tt_id' => $this->tt_id,
            'amount' => $this->amount,
        ]);

        return $dataProvider;
    }
}
