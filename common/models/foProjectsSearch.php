<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

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
     * Условия отбора, доступные перевозчикам
     */
    const CLAUSE_FOR_FERRYMAN_PAID = 1;
    const CLAUSE_FOR_FERRYMAN_TTN = 2;

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
            [['id', 'created_at', 'type_id', 'ca_id', 'manager_id', 'state_id', 'searchForFerryman', 'searchPerPage'], 'integer'],
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
            'searchPerPage' => 'Записей', // на странице
        ];
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
     * Выполняет выборку проектов.
     * @param $params array массив параметров отбора
     * @return ActiveDataProvider
     */
    public function search($params)
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
            'query' => $query,
            'key' => 'id',
            'sort' => [
                'defaultOrder' => ['date_start' => SORT_DESC],
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

        // если запрос выполняет перевозчик, то ограничим выборку только по нему
        if (Yii::$app->user->can('ferryman')) {
            $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
            // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
            if ($ferryman == null) {$query->where('1 <> 1'); return $dataProvider;}
            $query->andWhere(['ADD_perevoz' => $ferryman->name_crm]);
        }

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage))
            if (Yii::$app->user->can('ferryman'))
                $this->searchPerPage = 100;
            else
                $this->searchPerPage = false;
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
        ]);

        if ($this->searchGroupProjectTypes === null && $this->state_id === null)
            if (!Yii::$app->user->can('ferryman')) $query->andFilterWhere([
                'and',
                ['in', 'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT', explode(',', DirectMSSQLQueries::PROJECTS_STATES_LOGIST_LIMIT)],
                ['in', 'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT', explode(',', DirectMSSQLQueries::PROJECTS_TYPES_LOGIST_LIMIT)],
            ]);
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

        // отбор за период по дате создания
        if ($this->searchCreatedFrom != null && $this->searchCreatedTo != null) {
            // если указаны обе даты
            $query->andFilterWhere(['between', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new \yii\db\Expression('CONVERT(datetime, \''. $this->searchCreatedFrom .'T00:00:00.000\', 126)'), new \yii\db\Expression('CONVERT(datetime, \''. $this->searchCreatedTo .'T23:59:59.999\', 126)')]);
        } else if ($this->searchCreatedFrom != null && $this->searchCreatedTo == null) {
            // если указано только начало периода
            $query->andFilterWhere(['>=', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new \yii\db\Expression('CONVERT(datetime, \''. $this->searchCreatedFrom .'T00:00:00.000\', 126)')]);
        } else if ($this->searchCreatedFrom == null && $this->searchCreatedTo != null) {
            // если указан только конец периода
            $query->andFilterWhere(['<=', 'LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT', new \yii\db\Expression('CONVERT(datetime, \''. $this->searchCreatedTo .'T23:59:59.999\', 126)')]);
        };

        // отбор за период по дате вывоза
        if ($this->searchVivozDateFrom != null && $this->searchVivozDateTo != null) {
            // если указаны обе даты
            $query->andFilterWhere(['between', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new \yii\db\Expression('CONVERT(datetime, \''. $this->searchVivozDateFrom .'T00:00:00.000\', 126)'), new \yii\db\Expression('CONVERT(datetime, \''. $this->searchVivozDateTo .'T23:59:59.999\', 126)')]);
        } else if ($this->searchVivozDateFrom != null && $this->searchVivozDateTo == null) {
            // если указано только начало периода
            $query->andFilterWhere(['>=', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new \yii\db\Expression('CONVERT(datetime, \''. $this->searchVivozDateFrom .'T00:00:00.000\', 126)')]);
        } else if ($this->searchVivozDateFrom == null && $this->searchVivozDateTo != null) {
            // если указан только конец периода
            $query->andFilterWhere(['<=', 'LIST_PROJECT_COMPANY.ADD_vivozdate', new \yii\db\Expression('CONVERT(datetime, \''. $this->searchVivozDateTo .'T23:59:59.999\', 126)')]);
        };

        if ($this->searchExcludeIds !== null)
            $query->andFilterWhere(['not in', 'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY', $this->searchExcludeIds]);

        return $dataProvider;
    }
}
