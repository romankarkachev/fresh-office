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
    const CAPTION_FOR_TABLE3 = 'Таблица 3. Количество документов в разрезе признака "Типовой".';

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
     * @return array
     */
    public function collapseTableByField($array, $tableNum, $columnName)
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
                    'table' . $tableNum . '_name' => $row[$columnName],
                    'table' . $tableNum . '_count' => 1,
                ];
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
            'allModels' => $this->collapseTableByField($dataArray, 1, 'managerProfileName'),
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
            'allModels' => $this->collapseTableByField($dataArray, 2, 'stateName'),
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
            'allModels' => $this->collapseTableByField($dataArray, 3, 'is_typical_form'),
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
     * @param $params array массив параметров выборки (условия отбора)
     * @return array
     */
    public function search($params)
    {
        $this->load($params);

        $query = Edf::find()->select([
            'edf.id',
            'state_id',
            'manager_id',
            'is_typical_form',
            'stateName' => 'edf_states.name',
            'managerProfileName' => 'managerProfile.name',
        ]);
        $query->joinWith(['state', 'managerProfile']);

        // уточняется запрос
        // добавляется условие, если оно есть
        // только для вызова массива запросов на текущую дату
        if ($this->searchPeriodStart != null or $this->searchPeriodEnd != null) {
            // если указаны обе даты
            $query->andWhere(['between', 'created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
        } else if ($this->searchPeriodStart != null && $this->searchPeriodEnd == null) {
            // если указан только начало периода
            $query->andWhere(['>=', 'created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
        } else if ($this->searchPeriodStart == null && $this->searchPeriodEnd != null) {
            // если указан только конец периода
            $query->andWhere(['<=', 'created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
        }

        return $query->asArray()->all();
    }
}
