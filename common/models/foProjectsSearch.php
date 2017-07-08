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
     * Группа типов проектов.
     * @var string
     */
    public $searchGroupProjectTypes;

    /**
     * Статусы проектов.
     * @var string
     */
    public $searchProjectStates;

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
            [['id', 'type_id', 'ca_id', 'manager_id', 'state_id', 'searchPerPage'], 'integer'],
            [['amount', 'cost'], 'number'],
            [['vivozdate', 'date_start', 'date_end', 'searchGroupProjectTypes', 'searchProjectStates'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            // для отбора
            'ca_id' => 'Контрагент',
            'searchGroupProjectTypes' => 'Типы проектов',
            'searchProjectStates' => 'Статус проектов',
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

        if ($this->searchGroupProjectTypes == null && $this->searchProjectStates == null)
            $query->andFilterWhere([
                'and',
                ['in', 'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT', explode(',', DirectMSSQLQueries::PROJECTS_STATES_LOGIST_LIMIT)],
                ['in', 'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT', explode(',', DirectMSSQLQueries::PROJECTS_TYPES_LOGIST_LIMIT)],
            ]);
        else {
            // проверим параметры отбора, которые может применять пользователь
            if ($this->searchGroupProjectTypes != null) {
                // указана группа типов проектов
                $groupsOfTypes = $this->fetchGroupProjectTypesIds();
                $key = array_search($this->searchGroupProjectTypes, array_column($groupsOfTypes, 'id'));
                if (false !== $key) {
                    $query->andFilterWhere(['in', 'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT', explode(',', $groupsOfTypes[$key]['types'])]);
                }
            }

            if ($this->searchProjectStates != null)
                $query->andFilterWhere(['in', 'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT', $this->searchProjectStates]);
        }

        if ($this->ca_id != null)
            $query->andFilterWhere(['in', 'LIST_PROJECT_COMPANY.ID_COMPANY', $this->ca_id]);

        return $dataProvider;
    }
}
