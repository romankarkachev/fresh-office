<?php

namespace common\models;

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
     * Идентификаторы проектов, которые необходимо исключить из выборки.
     * @var array
     */
    public $searchExcludeIds;

    /**
     * Период создания с ... по.
     * @var string
     */
    public $searchCreatedFrom;
    public $searchCreatedTo;

    /**
     * Группа типов проектов.
     * @var string
     */
    public $searchGroupProjectTypes;

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
            [['id', 'created_at', 'type_id', 'ca_id', 'manager_id', 'state_id', 'searchPerPage'], 'integer'],
            [['amount', 'cost'], 'number'],
            [['vivozdate', 'date_start', 'date_end', 'searchExcludeIds', 'searchGroupProjectTypes', 'searchCreatedFrom', 'searchCreatedTo'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ca_id' => 'Контрагент',
            'state_id' => 'Статус',
            // для отбора
            'searchExcludeIds' => 'Исключаемые проекты',
            'searchGroupProjectTypes' => 'Типы проектов',
            'searchCreatedFrom' => 'Период с',
            'searchCreatedTo' => 'Период по',
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
            'perevoz' => 'ADD_perevoz_new',
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
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR) AS amount, SUM(SS_PRICE_TOVAR) AS cost
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
                    'vivozdate',
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
                    'oplata',
                    'adres',
                    'dannie',
                    'ttn',
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

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage)) $this->searchPerPage = false;
        // --значения по-умолчанию

        $dataProvider->pagination = [
            'pageSize' => $this->searchPerPage,
        ];

        $query->andFilterWhere([
            //'id' => $this->id,
            'LIST_PROJECT_COMPANY.ID_COMPANY' => $this->ca_id,
            'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => $this->state_id,
        ]);

        if ($this->searchGroupProjectTypes === null && $this->state_id === null)
            $query->andFilterWhere([
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

        if ($this->searchExcludeIds !== null)
            $query->andFilterWhere(['not in', 'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY', $this->searchExcludeIds]);

        return $dataProvider;
    }
}
