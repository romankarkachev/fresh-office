<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ResponsibleFornewca;

/**
 * ResponsibleFornewcaSearch represents the model behind the search form about `common\models\ResponsibleFornewca`.
 */
class ResponsibleFornewcaSearch extends ResponsibleFornewca
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'responsible_id', 'ac_id'], 'integer'],
            [['responsible_name'], 'safe'],
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
        $query = ResponsibleFornewca::find();

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
            'responsible_id' => $this->responsible_id,
            'ac_id' => $this->ac_id,
        ]);

        $query->andFilterWhere(['like', 'responsible_name', $this->responsible_name]);

        return $dataProvider;
    }
}
