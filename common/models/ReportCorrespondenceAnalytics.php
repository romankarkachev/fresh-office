<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ReportCorrespondenceAnalytics - это набор отчетов для анализа доставки корреспонденции.
 */
class ReportCorrespondenceAnalytics extends Model
{
    /**
     * Режим выборки запросов на транспорт за период.
     */
    const MODE_PERIOD = 1;

    /**
     * Режим выборки запросов на транспорт на дату.
     */
    const MODE_ALL = 2;

    const CAPTION_FOR_TABLE1 = 'Таблица 1. Количество отправлений в разрезе способов доставки.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Среднее время доставки в разрезе способов доставки.';

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
            // Таблица 1. Количество отправлений в разрезе способов доставки.
            'table1_name' => 'Способ доставки',
            'table1_count' => 'Кол-во',

            // Таблица 2. Среднее время доставки в разрезе способов доставки.
            'table2_name' => 'Способ доставки',
            'table2_count' => 'Время',
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
     * Преобразует массив из параметров, сворачивая его по полю $columnName, суммируя при этом количество запросов на
     * транспорт.
     * @param $array array массив запросов на транспорт
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
     * @param $dataArray array массив запросов на транспорт
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable1($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportTRAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 1, 'pdName'),
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
     * @param $dataArray array массив запросов на транспорт
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable2($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportTRAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 2, 'regionName'),
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
     * Создает массив запросов на транспорт с такими полями:
     * id | state_id | state.name | region_id | region.name | responsible_id | responsible_name | periodicity_id | periodicity_name
     * @param $params array массив параметров выборки (условия отбора)
     * @param $mode integer режим: выборка запросов за указанный пользователем период или вообще всех
     * @return array
     */
    public function search($params, $mode = self::MODE_PERIOD)
    {
        $this->load($params);

        $query = CorrespondencePackages::find()->select([
            'correspondence_packages.id',
            'pd_id',
            'pdName' => 'post_delivery_kinds.name',
        ]);
        $query->joinWith(['pd']);

        if ($mode == self::MODE_PERIOD)
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

    /**
     * Создает массив отходов по запросам на транспорт, переданным в параметрах.
     * @param $params array массив параметров выборки (условия отбора)
     * @param $mode integer режим: выборка запросов за указанный пользователем период или вообще всех
     * @return array
     */
    public function searchWaste($params, $mode = self::MODE_PERIOD)
    {
        $this->load($params);

        $query = TransportRequestsWaste::find()->select([
            'transport_requests_waste.id',
            'fkko_id',
            'fkkoName' => 'CONCAT(fkko.fkko_code, " - ", fkko.fkko_name)',
        ]);
        $query->joinWith(['tr', 'fkko']);

        if ($mode == self::MODE_PERIOD)
            // уточняется запрос
            // добавляется условие, если оно есть
            // только для вызова массива запросов на текущую дату
            if ($this->searchPeriodStart != null or $this->searchPeriodEnd != null) {
                // если указаны обе даты
                $query->andWhere(['between', 'transport_requests.created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            } else if ($this->searchPeriodStart != null && $this->searchPeriodEnd == null) {
                // если указан только начало периода
                $query->andWhere(['>=', 'transport_requests.created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
            } else if ($this->searchPeriodStart == null && $this->searchPeriodEnd != null) {
                // если указан только конец периода
                $query->andWhere(['<=', 'transport_requests.created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            }

        $query->andWhere('fkko_id IS NOT NULL');

        return $query->asArray()->all();
    }

    /**
     * Создает массив типов транспорта по запросам на транспорт, переданным в параметрах.
     * @param $params array массив параметров выборки (условия отбора)
     * @param $mode integer режим: выборка запросов за указанный пользователем период или вообще всех
     * @return array
     */
    public function searchTransport($params, $mode = self::MODE_PERIOD)
    {
        $this->load($params);

        $query = TransportRequestsTransport::find()->select([
            'transport_requests_transport.id',
            'tt_id',
            'ttName' => 'transport_types.name',
        ]);
        $query->joinWith(['tr', 'tt']);

        if ($mode == self::MODE_PERIOD)
            // уточняется запрос
            // добавляется условие, если оно есть
            // только для вызова массива запросов на текущую дату
            if ($this->searchPeriodStart != null or $this->searchPeriodEnd != null) {
                // если указаны обе даты
                $query->andWhere(['between', 'transport_requests.created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            } else if ($this->searchPeriodStart != null && $this->searchPeriodEnd == null) {
                // если указан только начало периода
                $query->andWhere(['>=', 'transport_requests.created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
            } else if ($this->searchPeriodStart == null && $this->searchPeriodEnd != null) {
                // если указан только конец периода
                $query->andWhere(['<=', 'transport_requests.created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            }

        return $query->asArray()->all();
    }
}
