<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FerrymenBankCards;

/**
 * FerrymenBankCardsSearch represents the model behind the search form about `common\models\FerrymenBankCards`.
 */
class FerrymenBankCardsSearch extends FerrymenBankCards
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id'], 'integer'],
            [['cardholder', 'number', 'bank'], 'safe'],
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
        $query = FerrymenBankCards::find();

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
            'ferryman_id' => $this->ferryman_id,
        ]);

        $query->andFilterWhere(['like', 'cardholder', $this->cardholder])
            ->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'bank', $this->bank]);

        return $dataProvider;
    }
}
