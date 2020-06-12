<?php

namespace common\models;

use backend\controllers\PoTemplatesController;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PoAt;

/**
 * PoAtSearch represents the model behind the search form of `common\models\PoAt`.
 */
class PoAtSearch extends PoAt
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_active', 'company_id', 'ei_id', 'periodicity'], 'integer'],
            [['amount'], 'number'],
            [['comment', 'properties'], 'safe'],
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
    public function search($params, $route = PoTemplatesController::ROOT_URL_FOR_SORT_PAGING)
    {
        $query = PoAt::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'is_active',
                    'company_id',
                    'ei_id',
                    'amount',
                    'comment',
                    'properties',
                    'periodicity',
                    'companyName' => [
                        'asc' => [Companies::tableName() . '.name' => SORT_ASC],
                        'desc' => [Companies::tableName() . '.name' => SORT_DESC],
                    ],
                    'eiName' => [
                        'asc' => [PoEi::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEi::tableName() . '.name' => SORT_DESC],
                    ],
                    'eiRepHtml' => [
                        'asc' => [PoEig::tableName() . '.name' => SORT_ASC, PoEi::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEig::tableName() . '.name' => SORT_DESC, PoEi::tableName() . '.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['company', 'ei', 'eiGroup']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_active' => $this->is_active,
            'company_id' => $this->company_id,
            'ei_id' => $this->ei_id,
            'amount' => $this->amount,
            'periodicity' => $this->periodicity,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'properties', $this->properties]);

        return $dataProvider;
    }
}
