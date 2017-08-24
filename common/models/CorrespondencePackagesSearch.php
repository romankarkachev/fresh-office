<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CorrespondencePackages;

/**
 * CorrespondencePackagesSearch represents the model behind the search form about `common\models\CorrespondencePackages`.
 */
class CorrespondencePackagesSearch extends CorrespondencePackages
{
    /**
     * Группы типов проектов
     */
    const CLAUSE_STATE_ALL = 1; // все статусы
    const CLAUSE_STATE_PROCESS = 2; // только в работе
    const CLAUSE_STATE_FINISHED = 3; // только завершенные

    /**
     * Флаг для управления статусами в выборке.
     * @var bool
     */
    public $searchGroupProjectStates;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'ready_at', 'sent_at', 'fo_project_id', 'state_id', 'type_id', 'pd_id', 'searchGroupProjectStates'], 'integer'],
            [['customer_name', 'pad', 'track_num', 'other', 'comment'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            // для отбора
            'searchGroupProjectStates' => 'Группы статусов',
            'searchCreatedFrom' => 'Период с',
            'searchCreatedTo' => 'Период по',
            'searchPerPage' => 'Записей', // на странице
        ];
    }

    /**
     * Возвращает массив с идентификаторами статусов проектов по группам.
     * @return array
     */
    public static function fetchGroupProjectStatesIds()
    {
        return [
            [
                'id' => self::CLAUSE_STATE_ALL,
                'name' => 'Все',
            ],
            [
                'id' => self::CLAUSE_STATE_PROCESS,
                'name' => 'Только в работе',
            ],
            [
                'id' => self::CLAUSE_STATE_FINISHED,
                'name' => 'Только завершенные',
            ],
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
        $query = CorrespondencePackages::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'route' => 'correspondence-packages',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'ready_at',
                    'sent_at',
                    'fo_project_id',
                    'customer_name',
                    'state_id',
                    'type_id',
                    'pad',
                    'pd_id',
                    'track_num',
                    'other',
                    'comment',
                    'stateName' => [
                        'asc' => ['projects_states.name' => SORT_ASC],
                        'desc' => ['projects_states.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => ['projects_types.name' => SORT_ASC],
                        'desc' => ['projects_types.name' => SORT_DESC],
                    ],
                    'pdName' => [
                        'asc' => ['post_delivery_kinds.name' => SORT_ASC],
                        'desc' => ['post_delivery_kinds.name' => SORT_DESC],
                    ],
                ]
            ]
        ]);

        $this->load($params);
        $query->joinWith(['state', 'type', 'pd']);

        // по-умолчанию все видят пакеты документов только в работе
        if ($this->searchGroupProjectStates == null) {
            $this->searchGroupProjectStates = self::CLAUSE_STATE_PROCESS;
        }

        switch ($this->searchGroupProjectStates) {
            case self::CLAUSE_STATE_PROCESS:
                $query->andWhere(['not in', 'state_id', ProjectsStates::STATE_ЗАВЕРШЕНО]);
                break;
            case self::CLAUSE_STATE_FINISHED:
                $query->andWhere(['state_id' => ProjectsStates::STATE_ЗАВЕРШЕНО]);
                break;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'ready_at' => $this->ready_at,
            'sent_at' => $this->sent_at,
            'fo_project_id' => $this->fo_project_id,
            'state_id' => $this->state_id,
            'type_id' => $this->type_id,
            'pd_id' => $this->pd_id,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'pad', $this->pad])
            ->andFilterWhere(['like', 'track_num', $this->track_num])
            ->andFilterWhere(['like', 'other', $this->other])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
