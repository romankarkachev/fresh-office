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
            [['id', 'transport_id'], 'integer'],
            [['inspected_at', 'place', 'responsible'], 'safe'],
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
            'transport_id' => $this->transport_id,
            'inspected_at' => $this->inspected_at,
        ]);

        $query->andFilterWhere(['like', 'place', $this->place])
            ->andFilterWhere(['like', 'responsible', $this->responsible]);

        return $dataProvider;
    }
}
