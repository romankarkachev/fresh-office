<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProjectsRatings;
use yii\helpers\ArrayHelper;

/**
 * ProjectsRatingsSearch represents the model behind the search form about `common\models\ProjectsRatings`.
 */
class ProjectsRatingsSearch extends ProjectsRatings
{
    /**
     * @var integer идентификатор(ы) проекта(ов), по которым производится отбор
     */
    public $searchProjectIds;

    /**
     * @var integer признак, определяющий тип выборки - детализированная или свернутая
     */
    public $searchDetailed;

    /**
     * @var integer количество записей на странице (по-умолчанию - false)
     */
    public $searchPerPage;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'rated_at', 'rated_by', 'ca_id', 'project_id', 'searchDetailed', 'searchPerPage'], 'integer'],
            [['rate'], 'number'],
            [['comment', 'searchProjectIds'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchProjectIds' => 'ID проектов',
            'searchDetailed' => 'Детализировать',
            'searchPerPage' => 'Записей', // на странице
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
        $query = ProjectsRatings::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => '/projects/ratings',
            ],
            'sort' => [
                'route' => '/projects/ratings',
                'defaultOrder' => ['rated_at' => SORT_DESC],
                'attributes' => [
                    'rated_at',
                    'ratedByProfileName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'ca_id',
                    /*
                    'caName' => [
                        'asc' => ['COMPANY.COMPANY_NAME' => SORT_ASC],
                        'desc' => ['COMPANY.COMPANY_NAME' => SORT_DESC],
                    ],
                    */
                    'project_id',
                    'rate',
                    'comment',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['ratedByProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage)) {
            $this->searchPerPage = 100;
        }

        if (empty($this->searchDetailed)) {
            // признак детализации не установлен, отображаем средние оценки по проектам
            $query->select([
                'ca_id',
                'rate' => 'AVG(rate)',
                'ratesCount' => 'COUNT(id)',
            ]);
            $query->groupBy('ca_id');
            $this->searchProjectIds = null;
            $this->ca_id = null;
        }
        elseif (!empty($this->searchProjectIds)) {
            $query->andFilterWhere([
                'project_id' => explode(',', $this->searchProjectIds),
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'rated_at' => $this->rated_at,
            'rated_by' => $this->rated_by,
            'ca_id' => $this->ca_id,
            //'project_id' => $this->project_id,
            'rate' => $this->rate,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
