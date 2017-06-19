<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Transport;

/**
 * TransportSearch represents the model behind the search form about `common\models\Transport`.
 */
class TransportSearch extends Transport
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id', 'tt_id', 'tc_id'], 'integer'],
            [['vin', 'rn', 'trailer_rn', 'comment'], 'safe'],
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
        $query = Transport::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'transport',
            ],
            'sort' => [
                'route' => 'transport',
                'defaultOrder' => ['vin' => SORT_DESC],
                'attributes' => [
                    'id',
                    'vin',
                    'ferryman_id',
                    'tt_id',
                    'tc_id',
                    'rn',
                    'trailer_rn',
                    'comment',
                    'ttName' => [
                        'asc' => ['transport_types.name' => SORT_ASC],
                        'desc' => ['transport_types.name' => SORT_DESC],
                    ],
                    'tcName' => [
                        'asc' => ['technical_conditions.name' => SORT_ASC],
                        'desc' => ['technical_conditions.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['tt', 'tc']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ferryman_id' => $this->ferryman_id,
            'tt_id' => $this->tt_id,
            'tc_id' => $this->tc_id,
        ]);

        $query->andFilterWhere(['like', 'vin', $this->vin])
            ->andFilterWhere(['like', 'rn', $this->rn])
            ->andFilterWhere(['like', 'trailer_rn', $this->trailer_rn])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
