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
    const CLAUSE_STATE_DEFAULT = 1; // по умолчанию
    const CLAUSE_STATE_PROCESS = 2; // только в работе
    const CLAUSE_STATE_JUST_CREATED = 3; // только формирующиеся пакеты
    const CLAUSE_STATE_READY = 4; // только ожидают отправки
    const CLAUSE_STATE_SENT = 5; // только отправленные
    const CLAUSE_STATE_DELIVERED = 6; // только доставленные
    const CLAUSE_STATE_FINISHED = 7; // только завершенные
    const CLAUSE_STATE_ALL = 8; // все статусы

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
            [['id', 'created_at', 'is_manual', 'ready_at', 'sent_at', 'delivered_at', 'fo_project_id', 'fo_id_company', 'state_id', 'type_id', 'pd_id', 'manager_id', 'searchGroupProjectStates'], 'integer'],
            [['customer_name', 'pad', 'track_num', 'other', 'comment'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fo_id_company' => 'Контрагент',
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
                'id' => self::CLAUSE_STATE_DEFAULT,
                'name' => 'По умолчанию',
                'hint' => 'Формирование документов на отправку + Ожидает отправки',
            ],
            [
                'id' => self::CLAUSE_STATE_PROCESS,
                'name' => 'В работе',
                'hint' => 'Все, кроме доставленных и завершенных',
            ],
            [
                'id' => self::CLAUSE_STATE_JUST_CREATED,
                'name' => 'Формирование',
                'hint' => 'Формирование документов на отправку',
            ],
            [
                'id' => self::CLAUSE_STATE_READY,
                'name' => 'Ожидает отправки',
            ],
            [
                'id' => self::CLAUSE_STATE_SENT,
                'name' => 'Отправленные',
            ],
            [
                'id' => self::CLAUSE_STATE_DELIVERED,
                'name' => 'Доставленные',
            ],
            [
                'id' => self::CLAUSE_STATE_FINISHED,
                'name' => 'Завершенные',
            ],
            [
                'id' => self::CLAUSE_STATE_ALL,
                'name' => 'Все',
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
            'pagination' => [
                'route' => 'correspondence-packages',
                'pageSize' => 100,
            ],
            'sort' => [
                'route' => 'correspondence-packages',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'ready_at',
                    'sent_at',
                    'delivered_at',
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

        // по-умолчанию все видят пакеты документов только в статусах "Формирование документов на отправку" и "Ожидает отправки"
        if ($this->searchGroupProjectStates == null) {
            $this->searchGroupProjectStates = self::CLAUSE_STATE_DEFAULT;
        }

        switch ($this->searchGroupProjectStates) {
            case self::CLAUSE_STATE_DEFAULT:
                $query->andWhere(['in', 'state_id', [ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ, ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ]]);
                break;
            case self::CLAUSE_STATE_PROCESS:
                $query->andWhere(['not in', 'state_id', [ProjectsStates::STATE_ДОСТАВЛЕНО, ProjectsStates::STATE_ЗАВЕРШЕНО]]);
                break;
            case self::CLAUSE_STATE_SENT:
                $query->andWhere(['state_id' => ProjectsStates::STATE_ОТПРАВЛЕНО]);
                break;
            case self::CLAUSE_STATE_JUST_CREATED:
                $query->andWhere(['state_id' => ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ]);
                break;
            case self::CLAUSE_STATE_READY:
                $query->andWhere(['state_id' => ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ]);
                break;
            case self::CLAUSE_STATE_DELIVERED:
                $query->andWhere(['state_id' => ProjectsStates::STATE_ДОСТАВЛЕНО]);
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
            'is_manual' => $this->is_manual,
            'ready_at' => $this->ready_at,
            'sent_at' => $this->sent_at,
            'delivered_at' => $this->delivered_at,
            'fo_project_id' => $this->fo_project_id,
            'fo_id_company' => $this->fo_id_company,
            'state_id' => $this->state_id,
            'type_id' => $this->type_id,
            'pd_id' => $this->pd_id,
            'manager_id' => $this->manager_id,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'pad', $this->pad])
            ->andFilterWhere(['like', 'track_num', $this->track_num])
            ->andFilterWhere(['like', 'other', $this->other])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
