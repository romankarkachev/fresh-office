<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TendersTp;

/**
 * TendersTpSearch represents the model behind the search form of `common\models\TendersTp`.
 */
class TendersTpSearch extends TendersTp
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'tender_id', 'fkko_id'], 'integer'],
            [['fkko_name'], 'safe'],
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
        $query = TendersTp::find();

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
            'tender_id' => $this->tender_id,
            'fkko_id' => $this->fkko_id,
        ]);

        $query->andFilterWhere(['like', 'fkko_name', $this->fkko_name]);

        return $dataProvider;
    }
}
