<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductionShipment;

/**
 * ProductionShipmentSearch represents the model behind the search form of `common\models\ProductionShipment`.
 */
class ProductionShipmentSearch extends ProductionShipment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'transport_id', 'site_id', 'fo_project_id'], 'integer'],
            [['rn', 'subject', 'comment'], 'safe'],
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
    public function search($params, $route = 'production-shipment')
    {
        $tableName = ProductionShipment::tableName();
        $query = ProductionShipment::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => [$tableName . '.id' => SORT_ASC],
                        'desc' => [$tableName . '.id' => SORT_DESC],
                    ],
                    'created_at',
                    'created_by',
                    'rn',
                    'transport_id',
                    'site_id',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'transportRep' => [
                        'asc' => [TransportBrands::tableName() . '.name' => SORT_ASC],
                        'desc' => [TransportBrands::tableName() . '.name' => SORT_DESC],
                    ],
                    'ferrymanName' => [
                        'asc' => ['ferrymen.id' => SORT_ASC],
                        'desc' => ['ferrymen.id' => SORT_DESC],
                    ],
                    'siteName' => [
                        'asc' => [ProductionSites::tableName() . '.id' => SORT_ASC],
                        'desc' => [ProductionSites::tableName() . '.id' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['transport', 'brand', 'tt', 'ferryman', 'site']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'transport_id' => $this->transport_id,
            'site_id' => $this->site_id,
            'fo_project_id' => $this->fo_project_id,
        ]);

        $query->andFilterWhere(['like', 'rn', $this->rn])
            ->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
