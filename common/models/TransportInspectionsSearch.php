<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportInspections;

/**
 * TransportInspectionsSearch represents the model behind the search form about `common\models\TransportInspections`.
 */
class TransportInspectionsSearch extends TransportInspections
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'transport_id', 'tc_id'], 'integer'],
            [['inspected_at', 'place', 'responsible', 'comment'], 'safe'],
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
        $query = TransportInspections::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['inspected_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'transport_id',
                    'inspected_at',
                    'place',
                    'responsible',
                    'tc_id',
                    'comment',
                    'tcName' => [
                        'asc' => ['technical_conditions.name' => SORT_ASC],
                        'desc' => ['technical_conditions.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['tc']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'transport_id' => $this->transport_id,
            'inspected_at' => $this->inspected_at,
        ]);

        $query->andFilterWhere(['like', 'place', $this->place])
            ->andFilterWhere(['like', 'responsible', $this->responsible])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
