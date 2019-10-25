<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\OrganizationsBas;

/**
 * OrganizationsBasSearch represents the model behind the search form about `common\models\OrganizationsBas`.
 */
class OrganizationsBasSearch extends OrganizationsBas
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'org_id'], 'integer'],
            [['bank_an', 'bank_bik', 'bank_name', 'bank_ca'], 'safe'],
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
        $query = OrganizationsBas::find();

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
            'org_id' => $this->org_id,
        ]);

        $query->andFilterWhere(['like', 'bank_an', $this->bank_an])
            ->andFilterWhere(['like', 'bank_bik', $this->bank_bik])
            ->andFilterWhere(['like', 'bank_name', $this->bank_name])
            ->andFilterWhere(['like', 'bank_ca', $this->bank_ca]);

        return $dataProvider;
    }
}
