<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use backend\components\grid\TotalsColumn;

/**
 * ReportAnalyticsCurrentReponsible - это набор отчетов для анализа обращений (отчет строится только по текущим
 * ответственным, которые определяются по дополнительному запросу в MS SQL).
 */
class ReportAnalyticsCurrentReponsible extends Model
{
    /**
     * Режим запроса обращений за период.
     */
    const MODE_PERIOD = 1;

    /**
     * Режим запроса обращений на дату.
     */
    const MODE_ALL = 2;

    const CAPTION_FOR_TABLE1 = 'Таблица 1. Обращений за период по ответственным.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Обращений за период по источникам.';
    const CAPTION_FOR_TABLE3 = 'Таблица 3. Всего обращений по их статусам.';
    const CAPTION_FOR_TABLE4 = 'Таблица 4. Обращения по статусам клиентов.';
    const CAPTION_FOR_TABLE5 = 'Таблица 5. Обращения по ответственным в разрезе статусов.';
    const CAPTION_FOR_TABLE6 = 'Таблица 6. Обращения по источникам в разрезе стасусов.';

    /**
     * Дата начала периода.
     * @var string
     */
    public $searchPeriodStart;

    /**
     * Дата окончания периода.
     * @var string
     */
    public $searchPeriodEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchPeriodStart', 'searchPeriodEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            // Таблица 1. Всего обращений по ответственным.
            'table1_id' => 'ID',
            'table1_name' => 'Ответственный',
            'table1_count' => 'Кол-во',

            // Таблица 2. Всего обращений по источникам.
            'table2_id' => 'ID',
            'table2_name' => 'Источник обращения',
            'table2_count' => 'Кол-во',

            // Таблица 3. Всего обращений по их статусам.
            'table3_name' => 'Статус',
            'table3_count' => 'Кол-во',

            // Таблица 4. Всего обращений по статусам клиентов.
            'table4_name' => 'Статус',
            'table4_count' => 'Кол-во',

            // Таблица 5. Обращения по ответственным в разрезе статусов обращений.
            'table5_id' => 'ID',
            'table5_name' => 'Ответственный',
            'table5_total_state' => 'Всего',
            'table5_total_ca_state' => 'Всего',

            // Таблица 6. Обращения по источникам в разрезе стасусов обращений.
            'table6_id' => 'ID',
            'table6_name' => 'Источник обращения',
            'table6_total_state' => 'Всего',
            'table6_total_ca_state' => 'Всего',

            // для отбора
            'searchPeriodStart' => 'Начало периода',
            'searchPeriodEnd' => 'Конец периода',
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
     * Преобразует массив из параметров, сворачивая его по полю responsible_id (ID ответственного),
     * суммируя при этом количество обращений.
     * @param $appeals array массив обращений
     * @return array
     */
    public function period_calculateTableResponsible($appeals)
    {
        $result = [];

        foreach ($appeals as $index => $appeal) {
            $key = array_search($appeal['responsible_id'], array_column($result, 'table1_id'));
            if (false !== $key)
                // если ответственный уже есть в таблице, увеличим количество обращений
                $result[$key]['table1_count']++;
            else {
                // если ответственного нет в результирующем массиве, то просто добавляем его с единицей
                $result[] = [
                    'table1_id' => $appeal['responsible_id'],
                    'table1_name' => $appeal['responsible_name'],
                    'table1_count' => 1,
                ];
            }
        }

        return $result;
    }

    /**
     * Преобразует массив из параметров, сворачивая его по полю as_id (ID источника обращения),
     * суммируя при этом количество обращений.
     * @param $appeals array массив обращений
     * @return array
     */
    public function period_calculateTableAppealSources($appeals)
    {
        $result = [];

        foreach ($appeals as $index => $appeal) {
            $key = array_search($appeal['as_id'], array_column($result, 'table2_id'));
            if (false !== $key)
                // если источник обращения уже есть в таблице, увеличим количество обращений
                $result[$key]['table2_count']++;
            else {
                // если источника обращения нет в результирующем массиве, то просто добавляем его с единицей
                $result[] = [
                    'table2_id' => $appeal['as_id'],
                    'table2_name' => $appeal['as_name'],
                    'table2_count' => 1,
                ];
            }
        }

        return $result;
    }

    /**
     * Преобразует массив из параметров, сворачивая его по полю state_id (ID статуса обращения),
     * суммируя при этом количество обращений.
     * @param $appeals array массив обращений
     * @return array
     */
    public function all_calculateTableAppealStates($appeals)
    {
        $result = [];

        foreach ($appeals as $index => $appeal) {
            $key = array_search($appeal['state_name'], array_column($result, 'table3_name'));
            if (false !== $key)
                // если статус обращения уже есть в таблице, увеличим количество обращений
                $result[$key]['table3_count']++;
            else {
                // если статуса обращения нет в результирующем массиве, то просто добавляем его с единицей
                $result[] = [
                    'table3_name' => $appeal['state_name'],
                    'table3_count' => 1,
                ];
            }
        }

        return $result;
    }

    /**
     * Преобразует массив из параметров, сворачивая его по полю ca_state_id (ID статуса клиента),
     * суммируя при этом количество обращений.
     * @param $appeals array массив обращений
     * @return array
     */
    public function all_calculateTableCaStates($appeals)
    {
        $result = [];

        foreach ($appeals as $index => $appeal) {
            $key = array_search($appeal['ca_state_name'], array_column($result, 'table4_name'));
            if (false !== $key)
                // если статус клиента уже есть в таблице, увеличим количество обращений
                $result[$key]['table4_count']++;
            else {
                // если статуса клиента нет в результирующем массиве, то просто добавляем его с единицей
                $result[] = [
                    'table4_name' => $appeal['ca_state_name'],
                    'table4_count' => 1,
                ];
            }
        }

        return $result;
    }

    /**
     * Преобразует массив из параметров, сворачивая его по полю responsible_id (ID ответственного),
     * суммируя при этом количество по статусам обращений. Пример:
     * id | name | Новое | Отказ | Конверсия
     * 3  | Имя  |   4   |   3   |     8
     * @param $table_num integer номер таблицы
     * @param $appeals array массив обращений
     * @param $field_id string название поля с идентификатором показателя
     * @param $field_name string название поля с наименованием показателя
     * @param $sort_attributes array массив колонок, по которым возможна сортировка
     * @param $columns array массив колонок для GridView
     * @return array
     */
    public function all_calculateTables56($table_num, $appeals, $field_id, $field_name, &$sort_attributes, &$columns)
    {
        $result = [];

        $states_columns = [];

        // добавляем колонки со статусами обращений
        $columns[] = [
            'class' => TotalsColumn::className(),
            'attribute' => 'table' . $table_num . '_total_state',
            'headerOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '60'],
        ];
        $distinct_states = Appeals::fetchAppealStates();
        foreach ($distinct_states as $state) {
            $column_name = 'table' . $table_num . '_state' . $state['id'];
            $states_columns[$column_name] = null;
            $sort_attributes[] = $column_name;
            $columns[] = [
                'class' => TotalsColumn::className(),
                'attribute' => $column_name,
                'label' => '<small>' . Appeals::getIndepAppealStateName($state['id']) . '</small>',
                'encodeLabel' => false,
                'headerOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '60'],
            ];
        };
        unset($distinct_states);

        // добавляем колонки со статусами клиентов
        $columns[] = [
            'class' => TotalsColumn::className(),
            'attribute' => 'table' . $table_num . '_total_ca_state',
            'headerOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '60'],
        ];
        $distinct_states = Appeals::fetchCaStates();
        foreach ($distinct_states as $state) {
            $column_name = 'table' . $table_num . '_ca_state' . $state['id'];
            $states_columns[$column_name] = null;
            $sort_attributes[] = $column_name;
            $columns[] = [
                'class' => TotalsColumn::className(),
                'attribute' => $column_name,
                'label' => '<small>' . Appeals::getIndepCaStateName($state['id']) . '</small>',
                'encodeLabel' => false,
                'headerOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '60'],
            ];
        }

        foreach ($appeals as $index => $appeal) {
            $column_state_name = 'table' . $table_num . '_state' . $appeal['state_id'];
            $column_ca_state_name = 'table' . $table_num . '_ca_state' . $appeal['ca_state_id'];
            $column_total_state = 'table' . $table_num . '_total_state';
            $column_total_ca_state = 'table' . $table_num . '_total_ca_state';

            $key = array_search($appeal[$field_id], array_column($result, 'table' . $table_num . '_id'));
            if (false !== $key) {
                // если показатель уже есть в таблице, увеличим количество обращений
                // наращиваем количество по текущему статусу обращения
                if (isset($result[$key][$column_state_name]))
                    $result[$key][$column_state_name]++;
                else
                    $result[$key][$column_state_name] = 1;

                // наращиваем количество по текущему статусу клиента
                if (isset($result[$key][$column_ca_state_name]))
                    $result[$key][$column_ca_state_name]++;
                else
                    $result[$key][$column_ca_state_name] = 1;

                // наращиваем всего по статусам обращений
                if (isset($result[$key][$column_total_state]))
                    $result[$key][$column_total_state]++;
                else
                    $result[$key][$column_total_state] = 1;

                // наращиваем всего по статусам клиентов
                if (isset($result[$key][$column_total_ca_state]))
                    $result[$key][$column_total_ca_state]++;
                else
                    $result[$key][$column_total_ca_state] = 1;
            }
            else {
                // если показателя нет в результирующем массиве, то просто добавляем его с единицей
                $new_states = $states_columns;
                $new_states[$column_state_name] = 1;
                $new_states[$column_ca_state_name] = 1;
                $new_states[$column_total_state] = 1;
                $new_states[$column_total_ca_state] = 1;
                $result[] = ArrayHelper::merge([
                    'table' . $table_num . '_id' => $appeal[$field_id],
                    'table' . $table_num . '_name' => $appeal[$field_name],
                ], $new_states);
            }
        }

        return $result;
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $appeals array массив обращений
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable1($appeals)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => $this->period_calculateTableResponsible($appeals),
            'key' => 'table1_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table1_count' => SORT_DESC],
                'attributes' => [
                    'table1_id',
                    'table1_name',
                    'table1_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $appeals array массив обращений
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable2($appeals)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => $this->period_calculateTableAppealSources($appeals),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table2_count' => SORT_DESC],
                'attributes' => [
                    'table2_id',
                    'table2_name',
                    'table2_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $appeals array массив обращений
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable3($appeals)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => $this->all_calculateTableAppealStates($appeals),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table3_count' => SORT_DESC],
                'attributes' => [
                    'table3_name',
                    'table3_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $appeals array массив обращений
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable4($appeals)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => $this->all_calculateTableCaStates($appeals),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table4_count' => SORT_DESC],
                'attributes' => [
                    'table4_name',
                    'table4_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $appeals array массив обращений
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable($table_num, $appeals, $field_id, $field_name, &$columns)
    {
        $sort_attributes = [
            'table' . $table_num . '_name',
            'table' . $table_num . '_total_state',
            'table' . $table_num . '_total_ca_state',
        ];
        $columns = [
            [
                'attribute' => 'table' . $table_num . '_name',
                'headerOptions' => ['style' => 'vertical-align: middle;'],
                'footer' => '<strong>Итого:</strong>',
                'footerOptions' => ['class' => 'text-right'],
            ],
        ];
        $models = $this->all_calculateTables56($table_num, $appeals, $field_id, $field_name, $sort_attributes, $columns);

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table' . $table_num . '_name' => SORT_DESC],
                'attributes' => $sort_attributes,
            ],
        ]);
    }

    /**
     * Выполняет дополнение массива ответственными лицами.
     * @param $appeals array массив обращений
     * @param $ca_ids string идентификаторы контрагентов через запятую
     * @return array
     */
    public function addAppealsArrayWithResponsible($appeals, $ca_ids)
    {
        $result = $appeals;

        $query_text = '
SELECT COMPANY.ID_COMPANY AS ca_id, COMPANY_NAME AS ca_name, COMPANY.ID_MANAGER AS manager_id, MANAGER_NAME AS manager_name
FROM COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE COMPANY.ID_COMPANY IN (' . $ca_ids . ')';

        $responsible_array = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        //var_dump($responsible_array);
        if (count($responsible_array) > 0) {
            // дополним массив обращений ответственными
            foreach ($responsible_array as $responsible) {
                foreach ($result as $index => $appeal) {
                    if ($appeal['ca_id'] == $responsible['ca_id']) {
                        // всем заявкам проставляем этого ответственного
                        $result[$index]['ca_name'] = $responsible['ca_name'];
                        $result[$index]['responsible_id'] = intval($responsible['manager_id']);
                        $result[$index]['responsible_name'] = $responsible['manager_name'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Создает массив обращений с такими полями:
     * id | as_id | as_name | ca_state_id | ca_state_name | state_id | state_name | ca_id | ca_name | responsible_id | responsible_name
     * @param $params array
     * @param $mode integer режим: выборка обращений за период или же всех
     * @return array
     */
    public function search($params, $mode = self::MODE_PERIOD)
    {
        $this->load($params);

        $query = Appeals::find()->select(['appeals.id', 'as_id', 'as_name' => 'appeal_sources.name', 'ca_state_id', 'state_id', 'ca_id' => 'fo_id_company']);
        $query->leftJoin('appeal_sources', 'appeal_sources.id = appeals.as_id');

        if ($mode == self::MODE_PERIOD)
            // уточняется запрос
            // добавляется условие, если оно есть
            // только для вызова массива обращений на текущую дату
            if ($this->searchPeriodStart !== null or $this->searchPeriodEnd !== null) {
                if ($this->searchPeriodStart !== '' && $this->searchPeriodEnd !== '') {
                    // если указаны обе даты
                    $query->andWhere(['between', 'created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
                } else if ($this->searchPeriodStart !== '' && $this->searchPeriodEnd === '') {
                    // если указан только начало периода
                    $query->andWhere(['>=', 'created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
                } else if ($this->searchPeriodStart === '' && $this->searchPeriodEnd !== '') {
                    // если указан только конец периода
                    $query->andWhere(['<=', 'created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
                }
            }
        $appeals_array = $query->asArray()->all();

        // извлечем всех уникальных контрагентов в отдельный массив,
        // чтобы потом для каждого из них определить ответственного
        $distinct_counteragents = [];
        foreach ($appeals_array as $index => $appeal) {
            /* @var $appeal \common\models\Appeals */

            // добавляем контрагента в массив их уникальных идентификаторов
            if ($appeal['ca_id'] != null)
                if (!in_array($appeal['ca_id'], $distinct_counteragents))
                    $distinct_counteragents[] = $appeal['ca_id'];

            // подставим наименование статуса клиента
            $appeals_array[$index]['ca_state_name'] = Appeals::getIndepCaStateName($appeal['ca_state_id']);

            // подставим наименование статуса обращения
            $appeals_array[$index]['state_name'] = Appeals::getIndepAppealStateName($appeal['state_id']);
        }

        if (count($distinct_counteragents) > 0) {
            $appeals_array = $this->addAppealsArrayWithResponsible($appeals_array, implode(",", $distinct_counteragents));
        }

        return $appeals_array;
    }
}
