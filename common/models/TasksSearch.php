<?php

namespace common\models;

use Yii;
use yii\base\Model;
use backend\controllers\TasksController;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * TasksSearch represents the model behind the search form of `common\models\Tasks`.
 */
class TasksSearch extends Tasks
{
    /**
     * Источники данных для выборки задач
     */
    const TASK_SOURCE_WEB_APP = 1; // текущее веб-приложение
    const TASK_SOURCE_FO = 2; // CRM Fresh Office

    /**
     * @var integer источник данных для выборки задач
     */
    public $searchSource;

    /**
     * @var string поле, определяющее начало периода при отборе по дате создания
     */
    public $searchCreatedAtStart;

    /**
     * @var string поле, определяющее окончание периода при отборе по дате создания
     */
    public $searchCreatedAtEnd;

    /**
     * @var string поле, определяющее начало периода при отборе по дате начала события
     */
    public $searchStartAtStart;

    /**
     * @var string поле, определяющее окончание периода при отборе по дате начала собатия
     */
    public $searchStartAtEnd;

    /**
     * @var integer поле для отбора по количеству переносов задачи
     */
    public $searchPostponedCount;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'type_id', 'state_id', 'priority_id', 'start_at', 'finish_at', 'fo_ca_id', 'fo_cp_id', 'responsible_id', 'project_id', 'searchSource', 'searchPostponedCount'], 'integer'],
            [['fo_ca_name', 'fo_cp_name', 'purpose', 'solution', 'searchCreatedAtStart', 'searchCreatedAtEnd', 'searchStartAtStart', 'searchStartAtEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchSource' => 'Источник',
            'searchCreatedAtStart' => 'Создан с',
            'searchCreatedAtEnd' => 'Создан по',
            'searchStartAtStart' => 'Начало с',
            'searchStartAtEnd' => 'Начало по',
            'searchPostponedCount' => 'Переносов',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Возвращает массив, содержащий источники данных для выборки задач.
     * @return array
     */
    public static function fetchTasksSources()
    {
        return [
            [
                'id' => self::TASK_SOURCE_WEB_APP,
                'name' => 'Веб-приложение',
            ],
            [
                'id' => self::TASK_SOURCE_FO,
                'name' => 'Fresh Office',
            ],
        ];
    }

    /**
     * Делает выборку источников данных для задач и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::fetchTasksSources(), 'id', 'name');
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string $route маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = TasksController::ROOT_URL_FOR_SORT_PAGING)
    {
        $tableName = Tasks::tableName();

        // вложенный запрос для определения количества переносов задач
        $sqPostponed = TasksWt::find()->select('COUNT(*)')->where($tableName . '.id = ' . TasksWt::tableName() . '.task_id');

        $query = Tasks::find()->select([
            '*',
            'id' => $tableName . '.id',
            'postponedCount' => $sqPostponed,
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['start_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'type_id',
                    'state_id',
                    'priority_id',
                    'start_at',
                    'finish_at',
                    'fo_ca_id',
                    'fo_ca_name',
                    'fo_cp_id',
                    'fo_cp_name',
                    'responsible_id',
                    'project_id',
                    'purpose',
                    'solution',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => [TasksTypes::tableName() . '.name' => SORT_ASC],
                        'desc' => [TasksTypes::tableName() . '.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => [TasksStates::tableName() . '.name' => SORT_ASC],
                        'desc' => [TasksStates::tableName() . '.name' => SORT_DESC],
                    ],
                    'priorityName' => [
                        'asc' => [TasksPriorities::tableName() . '.name' => SORT_ASC],
                        'desc' => [TasksPriorities::tableName() . '.name' => SORT_DESC],
                    ],
                    'responsibleProfileName' => [
                        'asc' => ['responsibleProfile.name' => SORT_ASC],
                        'desc' => ['responsibleProfile.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'type', 'state', 'priority', 'responsibleProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // дополним условием отбора за период по дате создания
        // проверим, не нажимал ли пользователь кнопку отбора за текущий день
        if (isset($params['filter_today'])) {
            $this->searchStartAtStart = date('Y-m-d');
            $this->searchStartAtEnd = date('Y-m-d');
        }

        if (!empty($this->searchCreatedAtStart) || !empty($this->searchCreatedAtEnd)) {
            if (!empty($this->searchCreatedAtStart) && !empty($this->searchCreatedAtEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', Tasks::tableName() . '.created_at', strtotime($this->searchCreatedAtStart . ' 00:00:00'), strtotime($this->searchCreatedAtEnd . ' 23:59:59')]);
            } else if (!empty($this->searchCreatedAtStart) && empty($this->searchCreatedAtEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', Tasks::tableName() . '.created_at', strtotime($this->searchCreatedAtStart . ' 00:00:00')]);
            } else if (empty($this->searchCreatedAtStart) && !empty($this->searchCreatedAtEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', Tasks::tableName() . '.created_at', strtotime($this->searchCreatedAtEnd . ' 23:59:59')]);
            };
        }

        if (isset($params['filter_forgotten'])) {
            // применяется фильтр по забытым задачам
            $query->andFilterWhere(['>=', Tasks::tableName() . '.start_at', strtotime(date('Y-m-d') . ' 23:59:59')])
            ->andWhere('state_id <> ' . TasksStates::STATE_ВЫПОЛНЕНА);
        }
        else {
            // дополним условием отбора за период по дате начала события
            if (!empty($this->searchStartAtStart) || !empty($this->searchStartAtEnd)) {
                if (!empty($this->searchStartAtStart) && !empty($this->searchStartAtEnd)) {
                    // если указаны обе даты
                    $query->andFilterWhere(['between', Tasks::tableName() . '.start_at', strtotime($this->searchStartAtStart . ' 00:00:00'), strtotime($this->searchStartAtEnd . ' 23:59:59')]);
                } else if (!empty($this->searchStartAtStart) && empty($this->searchStartAtEnd)) {
                    // если указано только начало периода
                    $query->andFilterWhere(['>=', Tasks::tableName() . '.start_at', strtotime($this->searchStartAtStart . ' 00:00:00')]);
                } else if (empty($this->searchStartAtStart) && !empty($this->searchStartAtEnd)) {
                    // если указан только конец периода
                    $query->andFilterWhere(['<=', Tasks::tableName() . '.start_at', strtotime($this->searchStartAtEnd . ' 23:59:59')]);
                };
            }
        }

        // дополним условием отбора по количеству переносов
        if (!empty($this->searchPostponedCount)) {
            $query->andWhere([
                'postponedCount' => $this->searchPostponedCount,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'type_id' => $this->type_id,
            'state_id' => $this->state_id,
            'priority_id' => $this->priority_id,
            'start_at' => $this->start_at,
            'finish_at' => $this->finish_at,
            'fo_ca_id' => $this->fo_ca_id,
            'fo_cp_id' => $this->fo_cp_id,
            'responsible_id' => $this->responsible_id,
            'project_id' => $this->project_id,
        ]);

        $query->andFilterWhere(['like', 'fo_ca_name', $this->fo_ca_name])
            ->andFilterWhere(['like', 'fo_cp_name', $this->fo_cp_name])
            ->andFilterWhere(['like', 'purpose', $this->purpose])
            ->andFilterWhere(['like', 'solution', $this->solution]);

        return $dataProvider;
    }

    /**
     * Выборка задач из CRM.
     * @param array $params
     * @param string $route маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function searchFreshOffice($params, $route = TasksController::ROOT_URL_FOR_SORT_PAGING)
    {
        $tableName = foTasks::tableName();

        // вложенный запрос для определения количества переносов задач
        $sqPostponed = foTasksPostponed::find()->select('COUNT(*)')->where($tableName . '.[ID_CONTACT] = ' . foTasksPostponed::tableName() . '.[ID_CONTACT]');

        $query = foTasks::find()->select([
            'id' => $tableName . '.[ID_CONTACT]',
            'created_at' => $tableName . '.[DATE_CREATED]',
            //'createdByProfileName',
            'managerName' => foManagers::tableName() . '.[MANAGER_NAME]',
            'typeName' => foTasksTypes::tableName() . '.[DISCRIPTION_VID_CONTCT]',
            'stateName' => foTasksStates::tableName() . '.[DISCRIPTION_PRIZNAK_CONTACT]',
            'priorityName' => foTasksPriorities::tableName() . '.[STATUS_CONTACT]',
            'start_at' => $tableName . '.[DATA_CONTACT]',
            'finish_at' => $tableName . '.[DATA_END_CONTACT]',
            'solution' => $tableName . '.[REZULTAT_CONTACT]',
            'purpose' => $tableName . '.[PRIMECHANIE]',
            'responsibleProfileName' => '[responsible].[MANAGER_NAME]',
            'postponedCount' => $sqPostponed,
        ])->where([
            'or',
            [$tableName . '.TRASH' => null],
            [$tableName . '.TRASH' => 0],
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['start_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at' => [
                        'asc' => ['CAST(' . $tableName . '.[DATE_CREATED] AS date)' => SORT_ASC],
                        'desc' => ['CAST(' . $tableName . '.[DATE_CREATED] AS date)' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => [foTasksTypes::tableName() . '.[DISCRIPTION_VID_CONTCT]' => SORT_ASC],
                        'desc' => [foTasksTypes::tableName() . '.[DISCRIPTION_VID_CONTCT]' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => [foTasksStates::tableName() . '.[DISCRIPTION_PRIZNAK_CONTACT]' => SORT_ASC],
                        'desc' => [foTasksStates::tableName() . '.[DISCRIPTION_PRIZNAK_CONTACT]' => SORT_DESC],
                    ],
                    'priorityName' => [
                        'asc' => [foTasksPriorities::tableName() . '.[STATUS_CONTACT]' => SORT_ASC],
                        'desc' => [foTasksPriorities::tableName() . '.[STATUS_CONTACT]' => SORT_DESC],
                    ],
                    'start_at' => [
                        'asc' => ['CAST(' . $tableName . '.[DATA_CONTACT] AS date)' => SORT_ASC],
                        'desc' => ['CAST(' . $tableName . '.[DATA_CONTACT] AS date)' => SORT_DESC],
                    ],
                    'finish_at' => [
                        'asc' => ['CONVERT(DATETIME, ' . $tableName . '.[DATA_END_CONTACT])' => SORT_ASC],
                        'desc' => ['CONVERT(DATETIME, ' . $tableName . '.[DATA_END_CONTACT])' => SORT_DESC],
                        /*
                        'asc' => ['CAST(' . $tableName . '.[DATA_END_CONTACT] AS date)' => SORT_ASC],
                        'desc' => ['CAST(' . $tableName . '.[DATA_END_CONTACT] AS date)' => SORT_DESC],
                        */
                    ],
                    'purpose',
                    'solution',
                    'responsibleProfileName' => [
                        'asc' => ['[responsible].[MANAGER_NAME]' => SORT_ASC],
                        'desc' => ['[responsible].[MANAGER_NAME]' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['manager', 'type', 'state', 'priority', 'responsible']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // отбор за период по дате создания
        // проверим, не нажимал ли пользователь кнопку отбора за текущий день
        if (isset($params['filter_today'])) {
            $this->searchStartAtStart = date('Y-m-d');
            $this->searchStartAtEnd = date('Y-m-d');
        }

        if (!empty($this->searchCreatedAtStart) || !empty($this->searchCreatedAtEnd)) {
            if (!empty($this->searchCreatedAtStart) && !empty($this->searchCreatedAtEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', 'DATE_CREATED', new Expression('CONVERT(datetime, \'' . $this->searchCreatedAtStart . 'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \'' . $this->searchCreatedAtEnd . 'T23:59:59.999\', 126)')]);
            } else if (!empty($this->searchCreatedAtStart) && empty($this->searchCreatedAtEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', 'DATE_CREATED', new Expression('CONVERT(datetime, \'' . $this->searchCreatedAtStart . 'T00:00:00.000\', 126)')]);
            } else if (empty($this->searchCreatedAtStart) && !empty($this->searchCreatedAtEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', 'DATE_CREATED', new Expression('CONVERT(datetime, \'' . $this->searchCreatedAtEnd . 'T23:59:59.999\', 126)')]);
            }
        }

        // дополним условием отбора за период по дате начала события
        if (isset($params['filter_forgotten'])) {
            // применяется фильтр по забытым задачам
            $query->andFilterWhere(['>', 'DATA_CONTACT', new Expression('CONVERT(datetime, \'' . date('Y-m-d') . 'T23:59:59.000\', 126)')])
                ->andWhere($tableName . '.ID_PRIZNAK_CONTACT != ' . foTasksStates::STATE_ВЫПОЛНЕНА);
        }
        else {
            if (!empty($this->searchStartAtStart) || !empty($this->searchStartAtEnd)) {
                if (!empty($this->searchStartAtStart) && !empty($this->searchStartAtEnd)) {
                    // если указаны обе даты
                    $query->andFilterWhere(['between', 'DATA_CONTACT', new Expression('CONVERT(datetime, \'' . $this->searchStartAtStart . 'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \'' . $this->searchStartAtEnd . 'T23:59:59.999\', 126)')]);
                } else if (!empty($this->searchStartedAtStart) && empty($this->searchStartAtEnd)) {
                    // если указано только начало периода
                    $query->andFilterWhere(['>=', 'DATA_CONTACT', new Expression('CONVERT(datetime, \'' . $this->searchStartedAtStart . 'T00:00:00.000\', 126)')]);
                } else if (empty($this->searchStartedAtStart) && !empty($this->searchStartAtEnd)) {
                    // если указан только конец периода
                    $query->andFilterWhere(['<=', 'DATA_CONTACT', new Expression('CONVERT(datetime, \'' . $this->searchStartAtEnd . 'T23:59:59.999\', 126)')]);
                }
            }
        }

        $query->andFilterWhere([
            $tableName . '.ID_CONTACT' => $this->id,
            'DATE_CREATED' => $this->created_at,
            //'created_by' => $this->created_by, // ???
            'ID_VID_CONTACT' => $this->type_id,
            'ID_PRIZNAK_CONTACT' => $this->state_id,
            'ID_LIST_STATUS_CONTACT' => $this->priority_id,
            'DATA_CONTACT' => $this->start_at,
            'DATA_END_CONTACT' => $this->finish_at,
            $tableName . '.ID_COMPANY' => $this->fo_ca_id,
            'ID_CONTACT_MAN' => $this->fo_cp_id,
            'ID_MANAGER_EXE' => $this->responsible_id,
            'ID_LIST_PROJECT_COMPANY' => $this->project_id,
        ]);

        return $dataProvider;
    }
}
