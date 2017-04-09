<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ResponsibleSubstitutesSearch represents the model behind the search form about `common\models\ResponsibleSubstitutes`.
 */
class ResponsibleSubstitutesSearch extends ResponsibleSubstitutes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'required_id', 'substitute_id'], 'integer'],
            [['required_name', 'substitute_name'], 'safe'],
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
        $query = ResponsibleSubstitutes::find();

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
            'required_id' => $this->required_id,
            'substitute_id' => $this->substitute_id,
        ]);

        $query->andFilterWhere(['like', 'required_name', $this->required_name])
            ->andFilterWhere(['like', 'substitute_name', $this->substitute_name]);

        return $dataProvider;
    }
}
