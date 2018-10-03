<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\controllers\EcoTypesController;

/**
 * EcoTypesMilestonesSearch represents the model behind the search form about `common\models\EcoTypesMilestones`.
 */
class EcoTypesMilestonesSearch extends EcoTypesMilestones
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type_id', 'milestone_id', 'is_file_reqiured', 'is_affects_to_cycle_time', 'time_to_complete_required', 'order_no'], 'integer'],
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
     * @param $route string
     * @return ActiveDataProvider
     */
    public function search($params, $route = EcoTypesController::ROOT_URL_FOR_MILESTONES_SORT_PAGING)
    {
        $query = EcoTypesMilestones::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 100,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['type_id' => SORT_ASC, 'order_no' => SORT_ASC],
                'attributes' => [
                    'id',
                    'type_id',
                    'milestone_id',
                    'is_file_reqiured',
                    'is_affects_to_cycle_time',
                    'time_to_complete_required',
                    'order_no',
                    'typeName' => [
                        'asc' => [EcoTypes::tableName() . '.name' => SORT_ASC],
                        'desc' => [EcoTypes::tableName() . '.name' => SORT_DESC],
                    ],
                    'milestoneName' => [
                        'asc' => [EcoMilestones::tableName() . '.name' => SORT_ASC],
                        'desc' => [EcoMilestones::tableName() . '.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['type', 'milestone']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type_id' => $this->type_id,
            'milestone_id' => $this->milestone_id,
            'is_file_reqiured' => $this->is_file_reqiured,
            'is_affects_to_cycle_time' => $this->is_affects_to_cycle_time,
            'time_to_complete_required' => $this->time_to_complete_required,
            'order_no' => $this->order_no,
        ]);

        return $dataProvider;
    }
}
