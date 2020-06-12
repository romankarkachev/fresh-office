<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TenderFormsVarietiesKinds;

/**
 * TenderFormsVarietiesKindsSearch represents the model behind the search form of `common\models\TenderFormsVarietiesKinds`.
 */
class TenderFormsVarietiesKindsSearch extends TenderFormsVarietiesKinds
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'variety_id', 'kind_id'], 'integer'],
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
        $query = TenderFormsVarietiesKinds::find();
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
            'variety_id' => $this->variety_id,
            'kind_id' => $this->kind_id,
        ]);

        return $dataProvider;
    }
}
