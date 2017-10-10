<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

/**
 * ReportCorrespondenceAnalytics - это набор отчетов для анализа доставки корреспонденции.
 */
class ReportCorrespondenceAnalytics extends Model
{
    /**
     * Режим выборки пакетов корреспонденции за период.
     */
    const MODE_PERIOD = 1;

    /**
     * Режим выборки пакетов корреспонденции на дату.
     */
    const MODE_ALL = 2;

    const CAPTION_FOR_TABLE1 = 'Таблица 1. Количество отправлений в разрезе способов доставки.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Среднее время доставки в разрезе способов доставки.';
    const CAPTION_FOR_TABLE3 = 'Таблица 3. Среднее время на операции.';

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
            'table2_value' => 'Время',

            // Таблица 3. Среднее время на операции.
            'table3_name' => 'Операция',
            'table3_value' => 'Выполняется в среднем за',

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
     * Дополняет запрос условием, если применяется отбор за период.
     * @param $query \yii\db\ActiveQuery
     */
    private function addConditionIfPeriodFilterIs($query)
    {
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
    }

    /**
     * Преобразует массив из параметров, сворачивая его по полю $columnName, суммируя при этом количество пакетов
     * корреспонденции.
     * @param $array array массив пакетов корреспонденции
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
     * @param $dataArray array массив пакетов корреспонденции
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable1($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCorrespondenceAnalytics',
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
     * Формирует таблицу 2.
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable2()
    {
        $query = CorrespondencePackages::find()->select([
            'table2_id' => 'pd_id',
            'table2_name' => 'post_delivery_kinds.name',
            'table2_value' => new Expression('AVG(`delivered_at` - `created_at`)'),
        ]);
        $query->joinWith(['pd'])->where('delivered_at IS NOT NULL')->groupBy('pd_id');
        $this->addConditionIfPeriodFilterIs($query);

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCorrespondenceAnalytics',
            'allModels' => $query->asArray()->all(),
            'key' => 'table2_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table2_value' => SORT_ASC],
                'attributes' => [
                    'table2_id',
                    'table2_name',
                    'table2_value',
                ],
            ],
        ]);
    }

    /**
     * Формирует таблицу 3.
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable3()
    {
        $result = [];

        // Документы подготавливаются в среднем за
        $query = CorrespondencePackages::find()->where('ready_at IS NOT NULL');
        $this->addConditionIfPeriodFilterIs($query);
        $result[] = [
            'table3_name' => 'Подготовка отправлений',
            'table3_value' => $query->average('ready_at - created_at'),
        ];
        unset($query);

        // Документы отправляются в среднем за
        $query = CorrespondencePackages::find()->where('sent_at IS NOT NULL');
        $this->addConditionIfPeriodFilterIs($query);
        $result[] = [
            'table3_name' => 'Отправка документов',
            'table3_value' => $query->average('sent_at - ready_at'),
        ];
        unset($query);

        // Документы доставляются в среднем за
        $query = CorrespondencePackages::find()->where('delivered_at IS NOT NULL');
        $this->addConditionIfPeriodFilterIs($query);
        $result[] = [
            'table3_name' => 'Доставка документов',
            'table3_value' => $query->average('delivered_at - created_at'),
        ];
        unset($query);

        // Постоплатные проекты оплачиваются в среднем за
        $query = CorrespondencePackages::find()->where('paid_at IS NOT NULL');
        $this->addConditionIfPeriodFilterIs($query);
        $result[] = [
            'table3_name' => 'Оплата по постоплате',
            'table3_value' => $query->average('paid_at - created_at'),
        ];
        unset($query);

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCorrespondenceAnalytics',
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table3_value' => SORT_ASC],
                'attributes' => [
                    'table3_name',
                    'table3_value',
                ],
            ],
        ]);
    }

    /**
     * Создает массив пакетов корреспонденции с такими полями:
     * correspondence_packages.id | pd_id | post_delivery_kinds.name
     * @param $params array массив параметров выборки (условия отбора)
     * @param $mode integer режим: выборка пакетов за указанный пользователем период или вообще всех
     * @return array
     */
    public function search($params)
    {
        $this->load($params);

        $query = CorrespondencePackages::find()->select([
            'correspondence_packages.id',
            'pd_id',
            'pdName' => 'post_delivery_kinds.name',
        ]);
        $query->joinWith(['pd']);

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
