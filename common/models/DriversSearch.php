<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Drivers;

/**
 * DriversSearch represents the model behind the search form about `common\models\Drivers`.
 */
class DriversSearch extends Drivers
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
            [['id', 'ferryman_id'], 'integer'],
            [['surname', 'name', 'patronymic', 'driver_license', 'dl_issued_at', 'phone', 'pass_serie', 'pass_num', 'pass_issued_at', 'pass_issued_by', 'searchEntire'], 'safe'],
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
        $query = Drivers::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'ferrymen-drivers',
            ],
            'sort' => [
                'route' => 'ferrymen-drivers',
                'defaultOrder' => ['surname' => SORT_DESC],
                'attributes' => [
                    'id',
                    'ferryman_id',
                    'surname',
                    'name',
                    'patronymic',
                    'driver_license',
                    'driver_license_index',
                    'phone',
                    'ferrymanName' => [
                        'asc' => ['ferrymen.name' => SORT_ASC],
                        'desc' => ['ferrymen.name' => SORT_DESC],
                    ],
                ]
            ]
        ]);

        $this->load($params);
        $query->joinWith(['ferryman']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ferryman_id' => $this->ferryman_id,
        ]);

        if ($this->searchEntire != null && $this->searchEntire != '')
            $query->andFilterWhere([
                'or',
                ['like', 'surname', $this->searchEntire],
                ['like', 'name', $this->searchEntire],
                ['like', 'patronymic', $this->searchEntire],
                ['like', 'driver_license', $this->searchEntire],
                ['like', 'phone', $this->searchEntire],
            ]);
        else
            $query->andFilterWhere(['like', 'surname', $this->surname])
                ->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'patronymic', $this->patronymic])
                ->andFilterWhere(['like', 'driver_license', $this->driver_license])
                ->andFilterWhere(['like', 'phone', $this->phone]);

        return $dataProvider;
    }
}