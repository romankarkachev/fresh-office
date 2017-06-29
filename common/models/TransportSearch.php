<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Transport;

/**
 * TransportSearch represents the model behind the search form about `common\models\Transport`.
 */
class TransportSearch extends Transport
{
    /**
     * Универсальная переменная для поиска по всем полям.
     * @var string
     */
    public $searchEntire;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id', 'tt_id', 'brand_id'], 'integer'],
            [['vin', 'rn', 'trailer_rn', 'comment', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ferryman_id' => 'Перевозчик',
            'searchEntire' => 'Универсальный поиск',
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
        $query = Transport::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'ferrymen-transport',
            ],
            'sort' => [
                'route' => 'ferrymen-transport',
                'defaultOrder' => ['vin' => SORT_DESC],
                'attributes' => [
                    'id',
                    'vin',
                    'ferryman_id',
                    'tt_id',
                    'brand_id',
                    'rn',
                    'trailer_rn',
                    'comment',
                    'ferrymanName' => [
                        'asc' => ['ferrymen.name' => SORT_ASC],
                        'desc' => ['ferrymen.name' => SORT_DESC],
                    ],
                    'ttName' => [
                        'asc' => ['transport_types.name' => SORT_ASC],
                        'desc' => ['transport_types.name' => SORT_DESC],
                    ],
                    'brandName' => [
                        'asc' => ['transport_brands.name' => SORT_ASC],
                        'desc' => ['transport_brands.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['ferryman', 'tt', 'brand']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ferryman_id' => $this->ferryman_id,
            'tt_id' => $this->tt_id,
            'brand_id' => $this->brand_id,
        ]);

        if ($this->searchEntire != null && $this->searchEntire != '')
            $query->andFilterWhere([
                'or',
                ['like', 'vin', $this->searchEntire],
                ['like', 'rn', $this->searchEntire],
                ['like', 'trailer_rn', $this->searchEntire],
                ['like', 'transport_types.name', $this->searchEntire],
            ]);
        else
            $query->andFilterWhere(['like', 'vin', $this->vin])
                ->andFilterWhere(['like', 'rn', $this->rn])
                ->andFilterWhere(['like', 'trailer_rn', $this->trailer_rn])
                ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}