<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * ReportPbxAnalytics - это набор отчетов для анализа телефонии.
 */
class ReportPbxAnalytics extends Model
{
    const CAPTION_FOR_TABLE1 = 'Таблица 1. Общие показатели.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Распределение по регионам.';
    const CAPTION_FOR_TABLE3 = 'Таблица 3. Распределение по сотрудникам.';
    const CAPTION_FOR_TABLE4 = 'Таблица 4. Распределение по сайтам.';
    const CAPTION_FOR_TABLE5 = 'Таблица 5. Потерянные звонки.';

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
            'measure_all' => 'Все',
            'measure_new' => 'Новые',
            'measure_in' => 'Входящие',
            'measure_out' => 'Исходящие',

            // Таблица 1. Общие показатели.
            'table1_name' => 'Направление',
            'table1_count' => 'Кол-во',

            // Таблица 2. Распределение по регионам.
            'table2_name' => 'Регион',

            // Таблица 3. Распределение по сотрудникам.
            'table3_name' => 'Сотрудник',

            // Таблица 4. Распределение по сайтам.
            'table4_name' => 'Сайт',

            // Таблица 5. Потерянные звонки.
            'table5_name' => 'Статус',
            'table5_count' => 'Кол-во',

            // для отчета по задачам и проектам контрагентов
            'pbxht_id' => 'ID',
            'pbxht_name' => 'Контрагент',
            'pbxht_managerName' => 'Ответственный',
            'pbxht_projectsInProgressCount' => 'Незавершенные проекты',
            'pbxht_tasksCount' => 'Задачи',

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
     * @param $array
     * @param $fieldName
     * @param $fieldValue
     * @param $measureName string
     * @param $measureNew
     * @param $measureIn
     * @param $measureOut
     * @return array
     */
    public function attachToTable($array, $fieldName, $fieldValue, $measureNew, $measureIn=null, $measureOut=null, $measureName='measure_all')
    {
        $key = array_search($fieldValue, array_column($array, $fieldName));
        if (false !== $key) {
            // если имя суммируемой колонки уже есть в таблице, увеличим количество
            $array[$key][$measureName]++;
            if (!empty($measureNew)) $array[$key]['measure_new']++;
            if (!empty($measureIn)) $array[$key]['measure_in']++;
            if (!empty($measureOut)) $array[$key]['measure_out']++;
        }
        else {
            // если имени суммирумоей колонки нет в результирующем массиве, то просто добавляем его с единицей
            $array[] = [
                $fieldName => $fieldValue,
                $measureName => 1,
                'measure_new' => $measureNew,
                'measure_in' => $measureIn,
                'measure_out' => $measureOut,
            ];
        }

        return $array;
    }

    /**
     * Формирует данные для таблицы 1 и возвращает ее.
     * @param $params array массив условий, которые задал пользователь (например, отбор за период)
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable1($params)
    {
        $this->load($params);

        if (empty($this->searchPeriodStart) || empty($this->searchPeriodEnd)) {
            $this->searchPeriodStart = date('Y-m-d');
            $this->searchPeriodEnd = date('Y-m-d');
        }

        $query = pbxCalls::find();

        // возможный отбор за период
        if (!empty($this->searchPeriodStart) || !empty($this->searchPeriodEnd)) {
            if (!empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', pbxCalls::tableName() . '.calldate', $this->searchPeriodStart . ' 00:00:00', $this->searchPeriodEnd . ' 23:59:59']);
            }
            elseif (!empty($this->searchPeriodStart) && empty($this->searchPeriodEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', pbxCalls::tableName() . '.calldate', $this->searchPeriodStart . ' 00:00:00']);
            }
            elseif (empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', pbxCalls::tableName() . '.calldate', $this->searchPeriodEnd . ' 23:59:59']);
            }
        }

        $tableQuery = clone $query;
        $result[] = [
            'table1_id' => 1,
            'table1_name' => 'Новые',
            'table1_count' => $tableQuery->andWhere(['is_new' => true])->count(),
        ];

        $tableQuery = clone $query;
        $result[] = [
            'table1_id' => 2,
            'table1_name' => 'Внутренние',
            'table1_count' => $tableQuery
                ->andWhere(ArrayHelper::merge(['and'], pbxCallsSearch::callDirectionFieldSet('not like', 'channel')))
                ->andWhere(ArrayHelper::merge(['and'], pbxCallsSearch::callDirectionFieldSet('not like', 'dstchannel')))
                ->count(),
        ];

        $tableQuery = clone $query;
        $result[] = [
            'table1_id' => 3,
            'table1_name' => 'Исходящие',
            'table1_count' => $tableQuery->andWhere(ArrayHelper::merge(['or'], pbxCallsSearch::callDirectionFieldSet('like', 'dstchannel')))->count(),
        ];

        $tableQuery = clone $query;
        $result[] = [
            'table1_id' => 4,
            'table1_name' => 'Входящие',
            'table1_count' => $tableQuery->andWhere(ArrayHelper::merge(['or'], pbxCallsSearch::callDirectionFieldSet('like', 'channel')))->andWhere('accountcode <> "autodialer"')->count(),
        ];

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportPbxAnalytics',
            'allModels' => $result,
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
     * @param $params
     * @return array
     */
    public function search($params)
    {
        $this->load($params);

        $query = pbxCalls::find()->select([
            'table2_name' => 'numbers.region',
            'table4_name' => 'websites.name',
            'table5_name' => 'disposition',
            'src',
            'dst',
            'accountcode',
            'is_new',
            'channel',
            'dstchannel',
        ])->joinWith(['number', 'website']);

        if (empty($this->searchPeriodStart) || empty($this->searchPeriodEnd)) {
            $this->searchPeriodStart = date('Y-m-d');
            $this->searchPeriodEnd = date('Y-m-d');
        }

        // возможный отбор за период
        if (!empty($this->searchPeriodStart) || !empty($this->searchPeriodEnd)) {
            if (!empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', pbxCalls::tableName() . '.calldate', $this->searchPeriodStart . ' 00:00:00', $this->searchPeriodEnd . ' 23:59:59']);
            }
            elseif (!empty($this->searchPeriodStart) && empty($this->searchPeriodEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', pbxCalls::tableName() . '.calldate', $this->searchPeriodStart . ' 00:00:00']);
            }
            elseif (empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', pbxCalls::tableName() . '.calldate', $this->searchPeriodEnd . ' 23:59:59']);
            }
        }

        $array = $query->asArray()->all();

        // результирующие массивы, в которые будут собираться данные для таблиц
        $resultByDirection = $resultByRegion = $resultByWebsite = $resultByEmployee = $resultByState = [];

        // один раз делаем выборку внутренних номеров телефонов для будущей их идентификации по имени операторов
        $internalPhones = pbxInternalPhoneNumber::find()->select(['phone_number', 'employeeName' => 'employee.name'])->joinWith(['employee'])->asArray()->all();

        foreach ($array as $index => $row) {
            $regionName = $row['table2_name'];
            $array = explode('|', $regionName);
            if (is_array($array) && count($array) > 1) {
                $regionName = empty($array[2]) ? $array[1] : $array[2];
            }

            $stateName = $row['table5_name'];

            $measureNew = null;
            if ($row['is_new']) {
                $measureNew = 1;
                $resultByDirection = $this->attachToTable($resultByDirection, 'table1_name', 'Новые', $measureNew, null, null, 'table1_count');
            }

            // входящий звонок - это звонок, где channel начинается на любое из значений из массива, а поле dstchannel не начинается
            $measureIn = null;
            foreach (pbxCallsSearch::FIELD_VALUES_FOR_DIRECTION_DETECTION as $value) {
                if (strpos($row['channel'], $value) === 0 && strpos($row['dstchannel'], $value) === false && strpos($row['accountcode'], 'autodialer') === false) {
                    $pattern = "/^SIP\/(\d{3})/";
                    if (preg_match_all($pattern, $row['dstchannel'], $match)) {
                        if (!empty($match[1][0])) $employee = $match[1][0];
                    }

                    $measureIn++;
                    $resultByDirection = $this->attachToTable($resultByDirection, 'table1_name', 'Входящие', $measureIn, null, null, 'table1_count');
                    break;
                }
            }

            // исходящий звонок - это звонок, где channel не начинается на любое из значений массива, а dstchannel - начинается
            $measureOut = null;
            foreach (pbxCallsSearch::FIELD_VALUES_FOR_DIRECTION_DETECTION as $value) {
                if (strpos($row['channel'], $value) === false && strpos($row['dstchannel'], $value) === 0) {
                    $pattern = "/^SIP\/(\d{3})/";
                    if (preg_match_all($pattern, $row['channel'], $match)) {
                        if (!empty($match[1][0])) $employee = $match[1][0];
                    }

                    $measureOut++;
                    $resultByDirection = $this->attachToTable($resultByDirection, 'table1_name', 'Исходящие', $measureOut, null, null, 'table1_count');
                    break;
                }
            }

            // внутренний звонок - это звонок, где ни channel, ни dstchannel не начинаются ни на одно из значений массива
            foreach (pbxCallsSearch::FIELD_VALUES_FOR_DIRECTION_DETECTION as $value) {
                if (strpos($row['channel'], $value) === false && strpos($row['dstchannel'], $value) === 0) {
                    $pattern = "/^SIP\/(\d{3})/";
                    if (preg_match_all($pattern, $row['channel'], $match)) {
                        if (!empty($match[1][0])) $employee = $match[1][0];
                    }

                    $measureOut++;
                    $resultByDirection = $this->attachToTable($resultByDirection, 'table1_name', 'Исходящие', $measureOut, null, null, 'table1_count');
                    break;
                }
            }

            $resultByDirection = $this->attachToTable($resultByDirection, 'table1_name', 'Все', 1, null, null, 'table1_count');

            // заполним регионы
            $resultByRegion = $this->attachToTable($resultByRegion, 'table2_name', $regionName, $measureNew, $measureIn, $measureOut);

            // заполним сотрудников
            $key = array_search($employee, array_column($internalPhones, 'phone_number'));
            if (false !== $key) {
                $employee = $internalPhones[$key]['employeeName'] . ' (' . $employee . ')';
            }
            $resultByEmployee = $this->attachToTable($resultByEmployee, 'table3_name', $employee, $measureNew, $measureIn, $measureOut);

            // заполним сайты
            $resultByWebsite = $this->attachToTable($resultByWebsite, 'table4_name', $row['table4_name'], $measureNew);

            // заполним статусы
            $resultByState = $this->attachToTable($resultByState, 'table5_name', $stateName, $measureNew, $measureIn, $measureOut, 'table5_count');
        }

        $key = array_search('ANSWERED', array_column($resultByState, 'table5_name'));
        if (false !== $key) unset($resultByState[$key]);

        return [
            new ArrayDataProvider([
                'modelClass' => 'common\models\ReportPbxAnalytics',
                'allModels' => $resultByRegion,
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => ['table2_name' => SORT_ASC],
                    'attributes' => [
                        'table2_name',
                        'measure_all',
                        'measure_new',
                        'measure_in',
                        'measure_out',
                    ],
                ],
            ]),
            new ArrayDataProvider([
                'modelClass' => 'common\models\ReportPbxAnalytics',
                'allModels' => $resultByEmployee,
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => ['table3_name' => SORT_ASC],
                    'attributes' => [
                        'table3_name',
                        'measure_all',
                        'measure_new',
                        'measure_in',
                        'measure_out',
                    ],
                ],
            ]),
            new ArrayDataProvider([
                'modelClass' => 'common\models\ReportPbxAnalytics',
                'allModels' => $resultByWebsite,
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => ['table4_name' => SORT_ASC],
                    'attributes' => [
                        'table4_name',
                        'measure_all',
                        'measure_new',
                    ],
                ],
            ]),
            new ArrayDataProvider([
                'modelClass' => 'common\models\ReportPbxAnalytics',
                'allModels' => $resultByState,
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => ['table5_name' => SORT_ASC],
                    'attributes' => [
                        'table5_name',
                        'table5_count',
                    ],
                ],
            ]),
        ];
    }

    /**
     * Делает выборку для отчета по наличию задач и незавершенных проектов контрагентов, которые звонили за выбранный
     * период и были идентифицированы.
     * @param $params
     * @return ArrayDataProvider
     */
    public function searchCurrentTasks($params)
    {
        $this->load($params);

        // период не должен быть пустым, иначе будет неоправданно долго
        if (empty($this->searchPeriodEnd)) {
            $this->searchPeriodEnd = date('Y-m-d');
        }

        if (!$this->validate()) {
            return new ArrayDataProvider([
                'query' => foCompany::find()->where('0=1'),
            ]);
        }

        // подзапрос, подсчитывающий количество незавершенных проектов по контрагенту
        $projectsInProgressSubQuery = foProjects::find()
            ->select('COUNT(ID_LIST_PROJECT_COMPANY)')
            ->where('COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY')
            ->andWhere(['not in', 'ID_PRIZNAK_PROJECT', ProjectsStates::НАБОР_СТАТУСОВ_НЕЗАВЕРШЕННЫЕ_ПРОЕКТЫ]);

        // подзапрос, подсчитывающий количество задач за сегодня по контрагенту
        $tasksSubQuery = foCompanyTasks::find()
            ->select('COUNT(ID_CONTACT)')
            ->where('COMPANY.ID_COMPANY = LIST_CONTACT_COMPANY.ID_COMPANY')
            ->andWhere([
                'between',
                '[DATA_CONTACT]',
                new Expression('CONVERT(datetime, \''. $this->searchPeriodEnd .'T00:00:00.000\', 126)'),
                new Expression('CONVERT(datetime, \''. $this->searchPeriodEnd .'T23:59:59.000\', 126)'),
            ]);

        // основной запрос
        $data = foCompany::find()
            ->select([
                'pbxht_id' => 'COMPANY.ID_COMPANY',
                'pbxht_name' => 'COMPANY_NAME',
                'pbxht_managerName' => 'MANAGER_NAME',
                'pbxht_projectsInProgressCount' => $projectsInProgressSubQuery,
                'pbxht_tasksCount' => $tasksSubQuery,
            ])
            ->where([
                'COMPANY.ID_COMPANY' => pbxCalls::find()->select(['fo_ca_id'])
                    ->where(['between', pbxCalls::tableName() . '.calldate', $this->searchPeriodEnd . ' 00:00:00', $this->searchPeriodEnd . ' 23:59:59'])
                    ->andWhere(['not', ['fo_ca_id' => null]])
                    ->andWhere('fo_ca_id <> -1 AND fo_ca_id <> 0')
                    ->groupBy('fo_ca_id')
                    ->asArray()->column()
            ])
            ->joinWith(['manager'])
            ->orderBy('COMPANY_NAME')->asArray()->all();

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportPbxAnalytics',
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => ['pbxht_name' => SORT_ASC],
                'attributes' => [
                    'pbxht_id',
                    'pbxht_name',
                    'pbxht_managerName',
                    'pbxht_projectsInProgressCount',
                    'pbxht_tasksCount',
                ],
            ],
        ]);
    }
}
