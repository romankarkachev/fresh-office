<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportRequestsWaste;

/**
 * TransportRequestsWasteSearch represents the model behind the search form about `common\models\TransportRequestsWaste`.
 */
class TransportRequestsWasteSearch extends TransportRequestsWaste
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'tr_id', 'fkko_id', 'fkko_name', 'dc_id', 'packing_id', 'ags_id', 'unit_id'], 'integer'],
            [['measure'], 'number'],
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
        $query = TransportRequestsWaste::find();

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
            'fkko_id' => $this->fkko_id,
            'fkko_name' => $this->fkko_name,
            'dc_id' => $this->dc_id,
            'packing_id' => $this->packing_id,
            'ags_id' => $this->ags_id,
            'unit_id' => $this->unit_id,
            'measure' => $this->measure,
        ]);

        return $dataProvider;
    }
}
