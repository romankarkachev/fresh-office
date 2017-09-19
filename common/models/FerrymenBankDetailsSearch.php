<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FerrymenBankDetails;

/**
 * FerrymenBankDetailsSearch represents the model behind the search form about `common\models\FerrymenBankDetails`.
 */
class FerrymenBankDetailsSearch extends FerrymenBankDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id'], 'integer'],
            [['name_full', 'inn', 'kpp', 'ogrn', 'bank_an', 'bank_bik', 'bank_name', 'bank_ca', 'comment'], 'safe'],
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
        $query = FerrymenBankDetails::find();

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

        $query->andFilterWhere(['like', 'name_full', $this->name_full])
            ->andFilterWhere(['like', 'inn', $this->inn])
            ->andFilterWhere(['like', 'kpp', $this->kpp])
            ->andFilterWhere(['like', 'ogrn', $this->ogrn])
            ->andFilterWhere(['like', 'bank_an', $this->bank_an])
            ->andFilterWhere(['like', 'bank_bik', $this->bank_bik])
            ->andFilterWhere(['like', 'bank_name', $this->bank_name])
            ->andFilterWhere(['like', 'bank_ca', $this->bank_ca])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
