<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EcoProjectsMilestones;

/**
 * EcoProjectsMilestonesSearch represents the model behind the search form about `common\models\EcoProjectsMilestones`.
 */
class EcoProjectsMilestonesSearch extends EcoProjectsMilestones
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'milestone_id', 'is_file_reqiured', 'is_affects_to_cycle_time', 'time_to_complete_required', 'order_no', 'closed_at'], 'integer'],
            [['date_close_plan'], 'safe'],
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
        $query = EcoProjectsMilestones::find();

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
            'project_id' => $this->project_id,
            'milestone_id' => $this->milestone_id,
            'is_file_reqiured' => $this->is_file_reqiured,
            'is_affects_to_cycle_time' => $this->is_affects_to_cycle_time,
            'time_to_complete_required' => $this->time_to_complete_required,
            'order_no' => $this->order_no,
            'date_close_plan' => $this->date_close_plan,
            'closed_at' => $this->closed_at,
        ]);

        return $dataProvider;
    }
}
