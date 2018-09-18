<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\controllers\PbxInternalPhoneNumbersController;

/**
 * pbxInternalPhoneNumberSearch represents the model behind the search form about `common\models\pbxInternalPhoneNumber`.
 */
class pbxInternalPhoneNumberSearch extends pbxInternalPhoneNumber
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'department_id', 'employee_id'], 'integer'],
            [['phone_number'], 'safe'],
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
        $query = pbxInternalPhoneNumber::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => PbxInternalPhoneNumbersController::ROOT_URL_FOR_SORT_PAGING,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => PbxInternalPhoneNumbersController::ROOT_URL_FOR_SORT_PAGING,
                'defaultOrder' => ['phone_number' => SORT_ASC],
                'attributes' => [
                    'id',
                    'phone_number',
                    'department_id',
                    'employee_id',
                    'departmentName' => [
                        'asc' => ['department.name' => SORT_ASC],
                        'desc' => ['department.name' => SORT_DESC],
                    ],
                    'employeeName' => [
                        'asc' => ['employee.name' => SORT_ASC],
                        'desc' => ['employee.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['department', 'employee']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            pbxInternalPhoneNumber::tableName() . '.department_id' => $this->department_id,
            pbxInternalPhoneNumber::tableName() . '.employee_id' => $this->employee_id,
        ]);

        $query->andFilterWhere(['like', 'phone_number', $this->phone_number]);

        return $dataProvider;
    }
}
