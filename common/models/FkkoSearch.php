<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Fkko;

/**
 * FkkoSearch represents the model behind the search form about `common\models\Fkko`.
 */
class FkkoSearch extends Fkko
{
    /**
     * Поле отбора для универсального поиска (во всем полям).
     * @var string
     */
    public $searchEntire;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['fkko_code', 'fkko_name', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['searchEntire'] = 'Значение для поиска по всем полям';

        return $labels;
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
        $query = Fkko::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
                'route' => 'fkko',
            ],
            'sort' => [
                'route' => 'fkko',
                'defaultOrder' => ['fkko_code' => SORT_ASC]
            ]
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
        ]);

        if ($this->searchEntire != null)
            $query->andFilterWhere([
                'or',
                ['like', 'fkko_code', $this->searchEntire],
                ['like', 'fkko_name', $this->searchEntire],
            ]);
        else
            $query->andFilterWhere(['like', 'fkko_code', $this->fkko_code])
                ->andFilterWhere(['like', 'fkko_name', $this->fkko_name]);

        return $dataProvider;
    }
}
