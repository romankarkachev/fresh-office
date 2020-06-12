<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AppealSources;
use yii\helpers\ArrayHelper;

/**
 * AppealSourcesSearch represents the model behind the search form about `common\models\AppealSources`.
 */
class AppealSourcesSearch extends AppealSources
{
    /**
     * @var string поле для универсального поиска
     */
    public $searchEntire;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'search_field', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchEntire' => 'Универсальный поиск',
        ]);
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
        $query = AppealSources::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'search_field',
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
        ]);

        if (!empty($this->searchEntire)) {
            $query->andWhere([
                'or',
                ['like', 'id', $this->searchEntire],
                ['like', 'name', $this->searchEntire],
                ['like', 'search_field', $this->searchEntire],
            ]);
        }
        else {
            $query->andFilterWhere(['like', 'name', $this->name])
                ->orFilterWhere(['like', 'search_field', $this->search_field]);
        }

        return $dataProvider;
    }
}
