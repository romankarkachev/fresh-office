<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Модель для выборки из таблицы проектов Fresh Office.
 */
class foProjectsSearch extends foProjects
{
    /**
     * Группы типов проектов
     */
    const CLAUSE_GROUP_PROJECT_TYPES_I = 1; // заказ пред/пост, вывоз, самопривоз 3,5,4,6
    const CLAUSE_GROUP_PROJECT_TYPES_II = 2; // осмотр, выездные, производство 14,8,10
    const CLAUSE_GROUP_PROJECT_TYPES_III = 3; // фото/видео 7

    /**
     * Значения для условий отбора, доступные перевозчикам, по полям ADD_oplata или ADD_ttn
     */
    const CLAUSE_FOR_FERRYMAN_PAID = 1;
    const CLAUSE_FOR_FERRYMAN_TTN = 2;

    /**
     * Значения для условий отбора, доступных заказчику, по полю Статус
     */
    const CLAUSE_FOR_CUSTOMER_STATE_ACTIVE = 1;
    const CLAUSE_FOR_CUSTOMER_STATE_DONE = 2;

    /**
     * Идентификаторы проектов, которые необходимо исключить из выборки.
     * @var array
     */
    public $searchExcludeIds;

    /**
     * Идентификатор(ы) проекта(ов), по которым производится отбор.
     * @var string
     */
    public $searchId;

    /**
     * Период создания с ... по.
     * @var string
     */
    public $searchCreatedFrom;
    public $searchCreatedTo;

    /**
     * Период вывоза с ... по.
     * @var string
     */
    public $searchVivozDateFrom;
    public $searchVivozDateTo;

    /**
     * Группа типов проектов.
     * @var string
     */
    public $searchGroupProjectTypes;

    /**
     * Условия отбора для перевозчиков.
     * @var integer
     */
    public $searchForFerryman;

    /**
     * Условия отбора для заказчиков.
     * @var integer
     */
    public $searchForCustomerByState;

    /**
     * Количество записей на странице.
     * По-умолчанию - false.
     * @var integer
     */
    public $searchPerPage;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_name', 'ca_name', 'manager_name', 'state_name', 'perevoz', 'proizodstvo', 'oplata', 'adres', 'dannie', 'ttn', 'weight'], 'string'],
            [['id', 'created_at', 'type_id', 'ca_id', 'manager_id', 'state_id', 'searchForFerryman', 'searchForCustomerByState', 'searchPerPage'], 'integer'],
            [['amount', 'cost'], 'number'],
            [['vivozdate', 'date_start', 'date_end', 'searchExcludeIds', 'searchId', 'searchGroupProjectTypes', 'searchCreatedFrom', 'searchCreatedTo', 'searchVivozDateFrom', 'searchVivozDateTo'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'searchId' => 'ID',
            'ca_id' => 'Контрагент',
            'state_id' => 'Статус',
            // для отбора
            'searchExcludeIds' => 'Исключаемые проекты',
            'searchGroupProjectTypes' => 'Типы проектов',
            'searchCreatedFrom' => 'Период с',
            'searchCreatedTo' => 'Период по',
            'searchVivozDateFrom' => 'Дата вывоза с',
            'searchVivozDateTo' => 'Дата вывоза по',
            'searchForFerryman' => 'Прочие условия',
            'searchForCustomerByState' => 'Прочие условия',
            'searchPerPage' => 'Записей', // на странице
        ];
    }

    /**
     * Возвращает массив с порядком сортировки колонок по статусам.
     * @param $type integer тип проекта
     * @return array
     */
    public function fetchSortColumnOrders($type)
    {
        $states = [];

        // базовые статусы для всех типов проектов
        $baseStates = [
            ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА,
            ProjectsStates::STATE_ТРАНСПОРТ_ЗАКАЗАН,
            ProjectsStates::STATE_ЕДЕТ_К_ЗАКАЗЧИКУ,
            ProjectsStates::STATE_У_ЗАКАЗЧИКА,
            ProjectsStates::STATE_ЕДЕТ_НА_СКЛАД,
            ProjectsStates::STATE_НА_СКЛАДЕ,
            ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН,
        ];

        // базовые статусы для типов Заказ
        $baseOrderStates = [
            ProjectsStates::STATE_ОДОБРЕНО_ПРОИЗВОДСТВОМ,
            ProjectsStates::STATE_НЕСОВПАДЕНИЕ,
            ProjectsStates::STATE_ДОКУМЕНТЫ_НА_СОГЛАСОВАНИИ_У_КЛИЕНТА,
            ProjectsStates::STATE_ОТДАНО_НА_ПОДПИСЬ,
            ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
            ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ,
            ProjectsStates::STATE_ОТПРАВЛЕНО,
            ProjectsStates::STATE_ДОСТАВЛЕНО,
        ];

        switch ($type) {
            case ProjectsTypes::PROJECT_TYPE_ВЫВОЗ:
                $states = ArrayHelper::merge($baseStates, [
                    ProjectsStates::STATE_ЗАВЕРШЕНО,
                ]);
                break;
            case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                $states = [
                    ProjectsStates::STATE_СЧЕТ_ОЖИДАЕТ_ОПЛАТЫ,
                    ProjectsStates::STATE_ОПЛАЧЕНО,
                ];
                $states = ArrayHelper::merge($states, $baseStates);
                $states = ArrayHelper::merge($states, $baseOrderStates);
                $states = ArrayHelper::merge($states, [
                    ProjectsStates::STATE_ЗАВЕРШЕНО,
                ]);
                break;
            case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                $states = ArrayHelper::merge($baseStates, $baseOrderStates);
                $states = ArrayHelper::merge($states, [
                    ProjectsStates::STATE_ОПЛАЧЕНО,
                    ProjectsStates::STATE_ЗАВЕРШЕНО,
                ]);
                break;
        }

        $states = ArrayHelper::merge($states, [
            ProjectsStates::STATE_НЕВЕРНОЕ_ОФОРМЛЕНИЕ_ЗАЯВКИ,
            ProjectsStates::STATE_ОТЛОЖЕНО_КЛИЕНТОМ,
            ProjectsStates::STATE_ОТКАЗ_КЛИЕНТА,
            ProjectsStates::STATE_ДЕЖУРНЫЙ_МЕНЕДЖЕР,
        ]);

        $result = [];
        $iterator = 0;
        foreach ($states as $key => $value) {
            $result[$value] = $iterator;
            $iterator++;
        }

        // для всех типов в конец добавляем эти странные статусы
        $result[ProjectsStates::STATE_СЭД] = 9998;
        $result[ProjectsStates::STATE_ОПАНЬКИ] = 9999;

        return $result;
    }

    /**
     * Возвращает массив с идентификаторами типов проектов по группам.
     * @return array
     */
    public static function fetchGroupProjectTypesIds()
    {
        return [
            [
                'id' => self::CLAUSE_GROUP_PROJECT_TYPES_I,
                'name' => 'Пред(пост)оплата, Вывоз, Самопривоз',
                'types' => '3,5,4,6',
            ],
            [
                'id' => self::CLAUSE_GROUP_PROJECT_TYPES_II,
                'name' => 'Осмотр, Выездные, Производство',
                'types' => '14,8,10',
            ],
            [
                'id' => self::CLAUSE_GROUP_PROJECT_TYPES_III,
                'name' => 'Фото/видео',
                'types' => '7',
            ],
        ];
    }

    /**
     * Возвращает массив с идентификаторами типов проектов для матрицы по статусам.
     * @return array
     */
    public static function fetchGroupProjectTypesIdsForMatrix()
    {
        return ArrayHelper::map(ProjectsTypes::find()->where(['id' => [
            ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
            ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
            ProjectsTypes::PROJECT_TYPE_ВЫВОЗ
        ]])->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * Возвращает массив с возможными условиями отбора, доступными перевозчикам.
     * @return array
     */
    public static function fetchGroupSearchForFerryman()
    {
        return [
            [
                'id' => self::CLAUSE_FOR_FERRYMAN_PAID,
                'name' => 'Неоплаченные',
            ],
            [
                'id' => self::CLAUSE_FOR_FERRYMAN_TTN,
                'name' => 'Без ТТН',
            ],
        ];
    }

    /**
     * Возвращает массив с возможными условиями отбора, доступными заказчикам.
     * @return array
     */
    public static function fetchGroupSearchForCustomer()
    {
        return [
            [
                'id' => self::CLAUSE_FOR_CUSTOMER_STATE_ACTIVE,
                'name' => 'Действующие',
            ],
            [
                'id' => self::CLAUSE_FOR_CUSTOMER_STATE_DONE,
                'name' => 'Завершенные',
            ],
        ];
    }

    /**
     * @param $array
     * @param $key
     * @return bool
     */
    public function removeSortColumn(&$array, $key) {
        return array_walk($array, function (&$v) use ($key) {
            if (isset($v[$key])) unset($v[$key]);
        });
    }

    /**
     * Выполняет выборку проектов.
     * @param $params array массив параметров отбора
     * @param $calculateTotalAmount bool возвратить массив или один только ActiveDataProvider
     * @return array|ActiveDataProvider
     */
    public function search($params, $calculateTotalAmount = null)
    {
        $query = foProjects::find();
        $query->select([
            'id' => 'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY',
            'created_at' => 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT',
            'type_id' => 'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT',
            'type_name' => 'LIST_SPR_PROJECT.NAME_PROJECT',
            'vivozdate' => 'ADD_vivozdate',
            'date_start' => 'DATE_START_PROJECT',
            'date_end' => 'DATE_FINAL_PROJECT',
            'ca_id' => 'LIST_PROJECT_COMPANY.ID_COMPANY',
            'ca_name' => 'COMPANY.COMPANY_NAME',
            'manager_id' => 'LIST_PROJECT_COMPANY.ID_MANAGER_VED',
            'manager_name' => 'MANAGERS.MANAGER_NAME',
            'payment.amount',
            'payment.cost',
            'state_id' => 'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT',
            'state_name' => 'LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT',
            'perevoz' => 'ADD_perevoz',
            'proizodstvo' => 'ADD_proizodstvo',
            'oplata' => 'ADD_oplata',
            'adres' => 'ADD_adres',
            'dannie' => 'ADD_dannie',
            'ttn' => 'ADD_ttn',
            'weight' => 'ADD_wieght',
        ]);
        $query->leftJoin('LIST_SPR_PROJECT', 'LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT');
        $query->leftJoin('LIST_SPR_PRIZNAK_PROJECT', 'LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT');
        $query->leftJoin('COMPANY', 'COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY');
        $query->leftJoin('MANAGERS', 'MANAGERS.ID_MANAGER = LIST_PROJECT_COMPANY.ID_MANAGER_VED');
        $query->leftJoin('(
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR * KOLVO) AS amount, SUM(SS_PRICE_TOVAR * KOLVO) AS cost
	FROM CBaseCRM_Fresh_7x.dbo.LIST_TOVAR_PROJECT
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS payment', 'payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY');

        $dataProvider = new ActiveDataProvider([
            'key' => 'id',
            'query' => $query,
            'pagination' => [
                'route' => isset($params['route']) ? $params['route'] : 'projects',
            ],
            'sort' => [
                'defaultOrder' => ['date_start' => SORT_DESC],
                'route' => isset($params['route']) ? $params['route'] : 'projects',
                'attributes' => [
                    'id' => [
                        'asc' => ['LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY' => SORT_ASC],
                        'desc' => ['LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY' => SORT_DESC],
                    ],
                    'created_at',
                    'type_id',
                    'type_name' => [
                        'asc' => ['LIST_SPR_PROJECT.NAME_PROJECT' => SORT_ASC],
                        'desc' => ['LIST_SPR_PROJECT.NAME_PROJECT' => SORT_DESC],
                    ],
                    'state_id',
                    'state_name' => [
                        'asc' => ['LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT' => SORT_ASC],
                        'desc' => ['LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT' => SORT_DESC],
                    ],
                    'manager_id',
                    'manager_name' => [
                        'asc' => ['MANAGERS.MANAGER_NAME' => SORT_ASC],
                        'desc' => ['MANAGERS.MANAGER_NAME' => SORT_DESC],
                    ],
                    'ca_id',
                    'ca_name' => [
                        'asc' => ['COMPANY.COMPANY_NAME' => SORT_ASC],
                        'desc' => ['COMPANY.COMPANY_NAME' => SORT_DESC],
                    ],
                    'amount',
                    'cost',
                    'vivozdate' => [
                        'asc' => ['ADD_vivozdate' => SORT_ASC],
                        'desc' => ['ADD_vivozdate' => SORT_DESC],
                    ],
                    'date_start' => [
                        'asc' => ['DATE_START_PROJECT' => SORT_ASC],
                        'desc' => ['DATE_START_PROJECT' => SORT_DESC],
                    ],
                    'date_end' => [
                        'asc' => ['DATE_FINAL_PROJECT' => SORT_ASC],
                        'desc' => ['DATE_FINAL_PROJECT' => SORT_DESC],
                    ],
                    'perevoz',
                    'proizodstvo',
                    'oplata' => [
                        'asc' => ['ADD_oplata' => SORT_ASC],
                        'desc' => ['ADD_oplata' => SORT_DESC],
                    ],
                    'adres',
                    'dannie',
                    'ttn' => [
                        'asc' => ['ADD_ttn' => SORT_ASC],
                        'desc' => ['ADD_ttn' => SORT_DESC],
                    ],
                    'weight' => [
                        'asc' => ['ADD_wieght' => SORT_ASC],
                        'desc' => ['ADD_wieght' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        // только не удаленные проекты
        $query->where([
            'or',
            ['LIST_PROJECT_COMPANY.TRASH' => null],
            ['LIST_PROJECT_COMPANY.TRASH' => 0],
        ]);

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage))
            if (Yii::$app->user->can('ferryman') || Yii::$app->user->can('customer'))
                $this->searchPerPage = 100;
            else
                $this->searchPerPage = false;

        if (!isset($this->searchForCustomerByState) && Yii::$app->user->can('customer')) $this->searchForCustomerByState = self::CLAUSE_FOR_CUSTOMER_STATE_ACTIVE;
        // --значения по-умолчанию

        $dataProvider->pagination = [
            'pageSize' => $this->searchPerPage,
        ];

        if ($this->searchId != null)
            $query->andFilterWhere([
                'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY' => explode(',', $this->searchId),
            ]);

        $query->andFilterWhere([
            'LIST_PROJECT_COMPANY.ID_COMPANY' => $this->ca_id,
            'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => $this->state_id,
            'LIST_PROJECT_COMPANY.ADD_perevoz' => $this->perevoz,
            'LIST_PROJECT_COMPANY.ADD_oplata' => $this->oplata,
        ]);

        if ($this->searchGroupProjectTypes === null && $this->state_id === null) {
            if (!Yii::$app->user->can('ferryman') && !Yii::$app->user->can('customer')) $query->andFilterWhere([
                'and',
                ['in', 'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT', explode(',', DirectMSSQLQueries::PROJECTS_STATES_LOGIST_LIMIT)],
                ['in', 'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT', explode(',', DirectMSSQLQueries::PROJECTS_TYPES_LOGIST_LIMIT)],
            ]);
        }
        else {
            // проверим параметры отбора, которые может применять пользователь
            if ($this->searchGroupProjectTypes !== null) {
                // указана группа типов проектов
                $groupsOfTypes = $this->fetchGroupProjectTypesIds();
                $key = array_search($this->searchGroupProjectTypes, array_column($groupsOfTypes, 'id'));
                if (false !== $key) {
                    $query->andFilterWhere(['in', 'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT', explode(',', $groupsOfTypes[$key]['types'])]);
                }
            }
        }

        // дополним условие отбора возможным значением, заданным перевозчиком
        if ($this->searchForFerryman != null)
            switch ($this->searchForFerryman) {
                case self::CLAUSE_FOR_FERRYMAN_PAID:
                    $query->andWhere(['ADD_oplata' => null]);
                    break;
                case self::CLAUSE_FOR_FERRYMAN_TTN:
                    $query->andWhere(['ADD_ttn' => null]);
                    break;
            }

        // дополним условие отбора возможным значением, заданным заказчиком
        switch ($this->searchForCustomerByState) {
            case self::CLAUSE_FOR_CUSTOMER_STATE_ACTIVE:
                $query->andWhere('LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT <> ' . ProjectsStates::STATE_ЗАВЕРШЕНО);
                break;
            case self::CLAUSE_FOR_CUSTOMER_STATE_DONE:
                $query->andWhere(['LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_ЗАВЕРШЕНО]);
                break;
        }

        // отбор за период по дате создания
        if ($this->searchCreatedFrom != null && $this->searchCreatedTo != null) {
            // если указаны обе даты
            $query->andFilterWhere(['between', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new Expression('CONVERT(datetime, \''. $this->searchCreatedFrom .'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \''. $this->searchCreatedTo .'T23:59:59.999\', 126)')]);
        } else if ($this->searchCreatedFrom != null && $this->searchCreatedTo == null) {
            // если указано только начало периода
            $query->andFilterWhere(['>=', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new Expression('CONVERT(datetime, \''. $this->searchCreatedFrom .'T00:00:00.000\', 126)')]);
        } else if ($this->searchCreatedFrom == null && $this->searchCreatedTo != null) {
            // если указан только конец периода
            $query->andFilterWhere(['<=', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new Expression('CONVERT(datetime, \''. $this->searchCreatedTo .'T23:59:59.999\', 126)')]);
        };

        // отбор за период по дате вывоза
        if ($this->searchVivozDateFrom != null && $this->searchVivozDateTo != null) {
            // если указаны обе даты
            $query->andFilterWhere(['between', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new Expression('CONVERT(datetime, \''. $this->searchVivozDateFrom .'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \''. $this->searchVivozDateTo .'T23:59:59.999\', 126)')]);
        } else if ($this->searchVivozDateFrom != null && $this->searchVivozDateTo == null) {
            // если указано только начало периода
            $query->andFilterWhere(['>=', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new Expression('CONVERT(datetime, \''. $this->searchVivozDateFrom .'T00:00:00.000\', 126)')]);
        } else if ($this->searchVivozDateFrom == null && $this->searchVivozDateTo != null) {
            // если указан только конец периода
            $query->andFilterWhere(['<=', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new Expression('CONVERT(datetime, \''. $this->searchVivozDateTo .'T23:59:59.999\', 126)')]);
        };

        if ($this->searchExcludeIds !== null)
            $query->andFilterWhere(['not in', 'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY', $this->searchExcludeIds]);

        $totalAmount = $query->sum('amount');
        if ($calculateTotalAmount === true)
            return [
                $dataProvider,
                $totalAmount,
            ];
        else
            return $dataProvider;
    }

    /**
     * @param $params array
     * @return array
     */
    public function searchForMatrix($params)
    {
        // колонки для GridView
        $columns = [
            [
                'attribute' => 'id',
                'label' => 'ID',
                'header' => 'ID',
            ],
        ];

        // массив атрибутов для сортировки у нас динамический, наполняется по ходу наполнения результирующего массива
        $sortAttributes = [
            'id',
        ];

        $query = foProjects::find();
        $query->select([
            'id' => 'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY',
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return [
                new ArrayDataProvider(),
                $columns,
            ];
        }

        // только не удаленные проекты
        $query->where([
            'or',
            ['LIST_PROJECT_COMPANY.TRASH' => null],
            ['LIST_PROJECT_COMPANY.TRASH' => 0],
        ]);

        // значения по-умолчанию
        // записей на странице - все
        if (empty($this->searchPerPage)) {
            $this->searchPerPage = 100;
        }

        // по-умолчанию первое число текущего месяца
        if (empty($this->searchCreatedFrom)) {
            $this->searchCreatedFrom = date('Y-m-01', time());
        }

        // по-умолчанию проекты с типом "Заказ предоплата"
        if (empty($this->searchGroupProjectTypes)) {
            $this->searchGroupProjectTypes = ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА;
        }

        // отбор за период по дате создания
        if (!empty($this->searchCreatedFrom) && !empty($this->searchCreatedTo)) {
            // если указаны обе даты
            $query->andFilterWhere(['between', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new Expression('CONVERT(datetime, \''. $this->searchCreatedFrom .'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \''. $this->searchCreatedTo .'T23:59:59.999\', 126)')]);
        } elseif (!empty($this->searchCreatedFrom) && empty($this->searchCreatedTo)) {
            // если указано только начало периода
            $query->andFilterWhere(['>=', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new Expression('CONVERT(datetime, \''. $this->searchCreatedFrom .'T00:00:00.000\', 126)')]);
        } elseif (empty($this->searchCreatedFrom) && !empty($this->searchCreatedTo)) {
            // если указан только конец периода
            $query->andFilterWhere(['<=', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new Expression('CONVERT(datetime, \''. $this->searchCreatedTo .'T23:59:59.999\', 126)')]);
        };

        $query->andFilterWhere([
            'ID_LIST_SPR_PROJECT' => $this->searchGroupProjectTypes,
            'LIST_PROJECT_COMPANY.ID_COMPANY' => $this->ca_id,
        ]);

        $projectsStates = foProjectsHistory::find()
            ->select([
                'project_id' => 'ID_LIST_PROJECT_COMPANY',
                'state_id' => 'LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT',
                'state_name' => 'PRIZNAK_PROJECT',
                'changed_at' => 'DATE_CHENCH_PRIZNAK'
            ])
            ->leftJoin(foProjectsStates::tableName(), 'LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_HISTORY_PROJECT_COMPANY.ID_PRIZNAK_PROJECT')
            ->where(['ID_LIST_PROJECT_COMPANY' => $query])
            ->andWhere('LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT <> ID_LIST_PROJECT_COMPANY')
            ->orderBy('LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT')
            ->asArray()->all();

        $projects = $query->asArray()->all();
        $sortOrders = self::fetchSortColumnOrders($this->searchGroupProjectTypes);

        // дополним массив проектов статусами
        foreach ($projectsStates as $state) {
            // ищем проект
            $columnName = 'state_' . $state['state_id'];
            $key = array_search($state['project_id'], array_column($projects, 'id'));
            if (false !== $key) {
                $projects[$key][$columnName] = Yii::$app->formatter->asDate($state['changed_at'], 'php:d.m.y') . "\r\n " . Yii::$app->formatter->asDate($state['changed_at'], 'php:H:i');
                $sortAttributes[] = $columnName;
            }

            $key = array_search($columnName, array_column($columns, 'attribute'));
            if (false === $key) {
                $columns[] = [
                    'attribute' => $columnName,
                    'label' => $state['state_name'],
                    'header' => $state['state_name'],
                    'format' => 'html',
                    'headerOptions' => ['class' => 'text-center small'],
                    'contentOptions' => ['class' => 'text-center small'],
                    'value' => function ($model, $key, $index, $column) {
                        /** @var \common\models\Projects $model */
                        /** @var \yii\grid\DataColumn $column */

                        return nl2br($model[$column->attribute]);
                    },
                    'sort' => ArrayHelper::getValue($sortOrders, $state['state_id'], 999),
                ];
            }
        }

        // сортируем статусы по полю sort и удаляем это поле у всех колонок (с ним не отрендерится таблица)
        ArrayHelper::multisort($columns, 'sort');
        $this->removeSortColumn($columns, 'sort');

        $dataProvider = new ArrayDataProvider([
            'allModels' => $projects,
            'key' => 'id', // поле, которое заменяет primary key
            'pagination' => [
                'route' => '/projects/states-matrix',
                'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => $sortAttributes,
            ],
        ]);

        return [
            $dataProvider,
            $columns,
        ];
    }

    /**
     * Выполняет выборку проектов для формирования электронной очереди.
     * @return ArrayDataProvider
     */
    public function searchForFreightsOnTheWay()
    {
        $query = foProjects::find()->select([
            'id' => foProjects::tableName() . '.ID_LIST_PROJECT_COMPANY',
            'created_at' => 'DATE_CREATE_PROGECT',
            'state_acquired_at' => foProjectsHistory::tableName() . '.DATE_CHENCH_PRIZNAK',
            'address' => 'ADD_adres',
            'destination' => 'ADD_proizodstvo',
            'data' => 'ADD_dannie',
            'ferryman' => 'ADD_perevoz',
        ])->where([
            'or',
            [foProjects::tableName() . '.TRASH' => null],
            [foProjects::tableName() . '.TRASH' => 0],
        ])->andWhere([
            foProjects::tableName() . '.ID_PRIZNAK_PROJECT' => [
                ProjectsStates::STATE_ЕДЕТ_НА_СКЛАД,
                ProjectsStates::STATE_НА_СКЛАДЕ,
            ],
            foProjects::tableName() . '.ID_LIST_SPR_PROJECT' => [
                ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
                ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
                ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
            ],
        ])->orderBy('DATE_CREATE_PROGECT DESC');
        $query->leftJoin(foProjectsHistory::tableName(), foProjectsHistory::tableName() . '.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AND ' . foProjectsHistory::tableName() . '.ID_PRIZNAK_PROJECT = ' . ProjectsStates::STATE_ЕДЕТ_НА_СКЛАД);

        $projects = $query->asArray()->all();

        $dataProvider = new ArrayDataProvider([
            'modelClass' => '\common\models\foProjects',
            'allModels' => $projects,
            'key' => 'id', // поле, которое заменяет primary key
            'pagination' => [
                'route' => 'freights-on-the-way',
            ],
        ]);

        return $dataProvider;
    }

    /**
     * @param $params array
     * @return ArrayDataProvider
     */
    public function searchMissingDriversTransport($params)
    {
        $query = foProjects::find()->select([
            'id' => foProjects::tableName() . '.ID_LIST_PROJECT_COMPANY',
            'created_at' => 'DATE_CREATE_PROGECT',
            'data' => 'ADD_dannie',
            'ferryman' => 'ADD_perevoz',
        ])->where([
            'or',
            [foProjects::tableName() . '.TRASH' => null],
            [foProjects::tableName() . '.TRASH' => 0],
        ])->andWhere([
            foProjects::tableName() . '.ID_LIST_SPR_PROJECT' => [
                ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
                ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
                ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
            ],
        ]);

        $dataProvider = new ArrayDataProvider([
            'modelClass' => '\common\models\foProjects',
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        // отбор за период по дате вывоза
        if (!empty($this->searchVivozDateFrom) || !empty($this->searchVivozDateTo)) {
            if (!empty($this->searchVivozDateFrom) && !empty($this->searchVivozDateTo)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new Expression('CONVERT(datetime, \''. $this->searchVivozDateFrom .'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \''. $this->searchVivozDateTo .'T23:59:59.999\', 126)')]);
            } else if (!empty($this->searchVivozDateFrom) && empty($this->searchVivozDateTo)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new Expression('CONVERT(datetime, \''. $this->searchVivozDateFrom .'T00:00:00.000\', 126)')]);
            } else if (empty($this->searchVivozDateFrom) && !empty($this->searchVivozDateTo)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new Expression('CONVERT(datetime, \''. $this->searchVivozDateTo .'T23:59:59.999\', 126)')]);
            }
        }
        else {
            $query->where('1 <> 1');
            return $dataProvider;
        }
        $query->orderBy('DATE_CREATE_PROGECT DESC');

        $projects = $query->asArray()->all();

        $dataProvider = new ArrayDataProvider([
            'modelClass' => '\common\models\foProjects',
            'allModels' => $projects,
            'key' => 'id', // поле, которое заменяет primary key
            'pagination' => [
                'route' => 'ferrymen/missing-drivers-transport',
                'pageSize' => 50,
            ],
        ]);

        return $dataProvider;
    }
}
