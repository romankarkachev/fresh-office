<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DriversInstructings;

/**
 * DriversInstructingsSearch represents the model behind the search form about `common\models\DriversInstructings`.
 */
class DriversInstructingsSearch extends DriversInstructings
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'driver_id'], 'integer'],
            [['instructed_at', 'place', 'responsible'], 'safe'],
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
        $query = DriversInstructings::find();

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
            'driver_id' => $this->driver_id,
            'instructed_at' => $this->instructed_at,
        ]);

        $query->andFilterWhere(['like', 'place', $this->place])
            ->andFilterWhere(['like', 'responsible', $this->responsible]);

        return $dataProvider;
    }
}
