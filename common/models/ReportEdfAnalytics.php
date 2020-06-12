<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ReportEdfAnalytics - это набор отчетов для анализа документооборота.
 */
class ReportEdfAnalytics extends Model
{
    const CAPTION_FOR_TABLE1 = 'Таблица 1. Количество документов в разрезе ответственных.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Количество документов в разрезе статусов.';
    const CAPTION_FOR_TABLE3 = 'Таблица 3. Количество типовых и нетиповых.';
    const CAPTION_FOR_TABLE4 = 'Таблица 4. Количество документов в разрезе контрагентов.';

    /**
     * @var string дата начала периода
     */
    public $searchPeriodStart;

    /**
     * @var string дата окончания периода
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
            // автоматические отправки:
            // Таблица 1. Количество отправлений в разрезе ответственных.
            'table1_name' => 'Ответственный',
            'table1_count' => 'Кол-во',

            // Таблица 2. Среднее время доставки в разрезе статусов.
            'table2_name' => 'Статус',
            'table2_count' => 'Кол-во',

            // Таблица 3. Количество документов в разрезе признака "Типовой".
            'table3_name' => 'Типовой',
            'table3_count' => 'Кол-во',

            // Таблица 3. Количество документов в разрезе контрагент.
            'table4_name' => 'Контрагент',
            'table4_count' => 'Кол-во',

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
     * Преобразует массив из параметров, сворачивая его по полю $columnName, суммируя при этом количество документов.
     * @param $array array массив документов
     * @param $tableNum string номер таблицы отчета
     * @param $columnName string наименование колонки, которая суммируется
     * @param $columnId string наименование колонки, содержащей идентификатор
     * @return array
     */
    public function collapseTableByField($array, $tableNum, $columnName, $columnId)
    {
        $result = [];

        foreach ($array as $index => $row) {
            $key = array_search($row[$columnName], array_column($result, 'table' . $tableNum . '_name'));
            if (false !== $key)
                // если имя суммируемой колонки уже есть в таблице, увеличим количество
                $result[$key]['table' . $tableNum . '_count']++;
            else {
                // если имени суммирумоей колонки нет в результирующем массиве, то просто добавляем его с единицей
                $result[] = [
                    'table' . $tableNum . '_id' => $row[$columnId],
                    'table' . $tableNum . '_name' => $row[$columnName],
                    'table' . $tableNum . '_count' => 1,
                ];
            }
        }

        return $result;
    }

    public function processTable4($array, $documentsTypes)
    {
        $result = [];
        $undefinedIds = [];

        foreach ($array as $item) {
            $name = '<не определено>';
            if (!empty($item['name_full'])) {
                $name = trim($item['name_full']);
            }
            elseif (!empty($item['name_short'])) {
                $name = trim($item['name_short']);
            }
            else {
                // ни одно поле с наименованием не заполнено, позднее выполним отдельную идентификацию
                $fo_ca_id = intval($item['fo_ca_id']);
                if (!empty($fo_ca_id)) {
                    $name = $fo_ca_id;
                    $undefinedIds[] = $fo_ca_id;
                }
            }

            $key = array_search($name, array_column($result, 'table4_name'));
            if (false !== $key) {
                $result[$key]['table4_measure' . $item['type_id']]++;
            }
            else {
                // контрагента еще нет в результирующей таблице, добавляем
                $newItem = [
                    'table4_id' => $item['id'],
                    'table4_name' => $name,
                ];
                foreach ($documentsTypes as $type) {
                    $value = 0;
                    if ($type['id'] == $item['type_id']) {
                        // повстречался как раз текущий тип документа, его-то и возьмем
                        // остальные создаются со значением 0
                        $value = 1;
                    }

                    $newItem['table4_measure' . $type['id']] = $value;
                }

                $result[] = $newItem;
                unset($newItem);
            }
            unset($key);
        }

        if (!empty($undefinedIds)) {
            foreach (foCompany::find()->where(['ID_COMPANY' => $undefinedIds])->all() as $undefinedCompany) {
                // найдем контрагента с текущим идентификатором в результирующей таблице и заменим идентификатор на наименование
                $key = array_search($undefinedCompany->ID_COMPANY, array_column($result, 'table4_name'));
                if (false !== $key) {
                    $result[$key]['table4_name'] = $undefinedCompany->COMPANY_NAME;
                }
            }
        }

        return $result;
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable1($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportEdfAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 1, 'managerProfileName', 'manager_id'),
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
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable2($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportEdfAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 2, 'stateName', 'state_id'),
            'key' => 'table2_id', // поле, которое заменяет primary key
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
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable3($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportEdfAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 3, 'is_typical_form', 'is_typical_form'),
            'key' => 'table3_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table3_count' => SORT_DESC],
                'attributes' => [
                    'table3_id',
                    'table3_name',
                    'table3_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return array
     */
    public function makeDataProviderForTable4($dataArray)
    {
        $documentsTypes = DocumentsTypes::find()->asArray()->all();
        $tableColumns = [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'table4_name'
        ];
        $sortColumns = [
            'defaultOrder' => ['table4_name' => SORT_ASC],
            'attributes' => [
                'table4_id',
                'table4_name',
            ],
        ];

        foreach ($documentsTypes as $type) {
            $tableColumns[] = [
                'attribute' => 'table4_measure' . $type['id'],
                'label' => $type['name'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '90'],
            ];
            $sortColumns['attributes'][] = 'table4_measure' . $type['id'];
        }

        return [
            new ArrayDataProvider([
                'modelClass' => 'common\models\ReportEdfAnalytics',
                'allModels' => $this->processTable4($dataArray, $documentsTypes),
                'key' => 'table4_id', // поле, которое заменяет primary key
                'pagination' => false,
                'sort' => $sortColumns,
            ]),
            $tableColumns,
        ];
    }

    /**
     * @param $params array массив параметров выборки (условия отбора)
     * @return array
     */
    public function search($params)
    {
        $this->load($params);

        $query = Edf::find()->select([
            'id' => Edf::tableName() . '.`id`',
            'type_id',
            'state_id',
            'manager_id',
            'fo_ca_id',
            'is_typical_form',
            'name_full' => 'req_name_full',
            'name_short' => 'req_name_short',
            'stateName' => 'edf_states.name',
            'managerProfileName' => 'managerProfile.name',
        ]);
        $query->joinWith(['state', 'managerProfile']);

        // уточняется запрос
        // добавляется условие, если оно есть
        // только для вызова массива запросов на текущую дату
        if (!empty($this->searchPeriodStart) || !empty($this->searchPeriodEnd)) {
            // если указаны обе даты
            $query->andWhere(['between', 'created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
        } else if (!empty($this->searchPeriodStart) && empty($this->searchPeriodEnd)) {
            // если указано только начало периода
            $query->andWhere(['>=', 'created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
        } else if (empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
            // если указан только конец периода
            $query->andWhere(['<=', 'created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
        }

        return $query->asArray()->all();
    }
}
