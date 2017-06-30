<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ResponsibleByProjectTypes;

/**
 * ResponsibleByProjectTypesSearch represents the model behind the search form about `common\models\ResponsibleByProjectTypes`.
 */
class ResponsibleByProjectTypesSearch extends ResponsibleByProjectTypes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'project_type_id'], 'integer'],
            [['project_type_name', 'receivers'], 'safe'],
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
        $query = ResponsibleByProjectTypes::find();

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
            'project_type_id' => $this->project_type_id,
        ]);

        $query->andFilterWhere(['like', 'project_type_name', $this->project_type_name])
            ->andFilterWhere(['like', 'receivers', $this->receivers]);

        return $dataProvider;
    }
}
