<?php

namespace common\models;

use backend\controllers\PbxEmployeesController;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\pbxEmployees;

/**
 * pbxEmployeeSearch represents the model behind the search form about `common\models\pbxEmployee`.
 */
class pbxEmployeesSearch extends pbxEmployees
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'department_id'], 'integer'],
            [['name'], 'safe'],
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
        $query = pbxEmployees::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => PbxEmployeesController::ROOT_URL_FOR_SORT_PAGING,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => PbxEmployeesController::ROOT_URL_FOR_SORT_PAGING,
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'department_id',
                    'departmentName' => [
                        'asc' => ['department.name' => SORT_ASC],
                        'desc' => ['department.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['department']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            pbxEmployees::tableName() . '.id' => $this->id,
            'department_id' => $this->department_id,
        ]);

        $query->andFilterWhere(['like', pbxEmployees::tableName() . '.name', $this->name]);

        return $dataProvider;
    }
}
