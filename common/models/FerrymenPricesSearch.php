<?php

namespace common\models;

use backend\controllers\FerrymenPricesController;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FerrymenPrices;

/**
 * FerrymenPricesSearch represents the model behind the search form of `common\models\FerrymenPrices`.
 */
class FerrymenPricesSearch extends FerrymenPrices
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id'], 'integer'],
            [['price', 'cost'], 'number'],
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
     * @param $route string маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = FerrymenPricesController::ROOT_URL_FOR_SORT_PAGING)
    {
        $query = FerrymenPrices::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['ferrymanName' => SORT_ASC],
                'attributes' => [
                    'id',
                    'ferryman_id',
                    'price',
                    'cost',
                    'ferrymanName' => [
                        'asc' => ['ferrymen.name' => SORT_ASC],
                        'desc' => ['ferrymen.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith('ferryman');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ferryman_id' => $this->ferryman_id,
            'price' => $this->price,
            'cost' => $this->cost,
        ]);

        return $dataProvider;
    }
}
