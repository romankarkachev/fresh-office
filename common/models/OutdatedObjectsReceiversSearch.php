<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\OutdatedObjectsReceivers;

/**
 * OutdatedObjectsReceiversSearch represents the model behind the search form of `common\models\OutdatedObjectsReceivers`.
 */
class OutdatedObjectsReceiversSearch extends OutdatedObjectsReceivers
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'section', 'time'], 'integer'],
            [['receiver'], 'safe'],
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
     * @param array $params
     * @param $route string
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'outdated-objects-receivers')
    {
        $query = OutdatedObjectsReceivers::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'attributes' => [
                    'id',
                    'section',
                    'time',
                    'receiver',
                    'periodicity',
                ],
            ],
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
            'section' => $this->section,
            'time' => $this->time,
        ]);

        $query->andFilterWhere(['like', 'receiver', $this->receiver]);

        return $dataProvider;
    }
}
