<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EcoMcTp;

/**
 * EcoMcTpSearch represents the model behind the search form of `common\models\EcoMcTp`.
 */
class EcoMcTpSearch extends EcoMcTp
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'mc_id', 'report_id'], 'integer'],
            [['date_deadline', 'date_fact'], 'safe'],
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
        $query = EcoMcTp::find();

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
            'mc_id' => $this->mc_id,
            'report_id' => $this->report_id,
            'date_deadline' => $this->date_deadline,
            'date_fact' => $this->date_fact,
        ]);

        return $dataProvider;
    }
}
