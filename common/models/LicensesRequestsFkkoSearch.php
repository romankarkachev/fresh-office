<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\LicensesRequestsFkko;

/**
 * LicensesRequestsFkkoSearch represents the model behind the search form about `common\models\LicensesRequestsFkko`.
 */
class LicensesRequestsFkkoSearch extends LicensesRequestsFkko
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'lr_id', 'fkko_id', 'file_id'], 'integer'],
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
        $query = LicensesRequestsFkko::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => '/licenses-requests/fkko-list',
            ],
            'sort' => [
                'route' => '/licenses-requests/fkko-list',
                'defaultOrder' => ['fkkoRep' => SORT_ASC],
                'attributes' => [
                    'id',
                    'fkkoRep' => [
                        'asc' => ['fkko.fkko_code' => SORT_ASC],
                        'desc' => ['fkko.fkko_code' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['fkko']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'lr_id' => $this->lr_id,
            'fkko_id' => $this->fkko_id,
            'file_id' => $this->file_id,
        ]);

        return $dataProvider;
    }
}
