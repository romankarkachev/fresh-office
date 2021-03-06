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
    const CAPTION_FOR_TABLE22 = 'Таблица 2. Количество отправлений в разрезе контрагентов.';
    const CAPTION_FOR_TABLE23 = 'Таблица 3. Количество отправлений в разрезе статусов.';
    const CAPTION_FOR_TABLE24 = 'Таблица 4. Среднее время на согласование по менеджерам.';

    /**
     * @var string дата начала периода
     */
    public $searchPeriodStart;

    /**
     * @var string дата окончания периода
     */
    public $searchPeriodEnd;

    /**
     * @var integer признак отбора по ручным пакетам
     */
    public $searchManual;

    /**
     * @var integer идентификатор менеджера
     */
    public $searchManager;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchManual', 'searchManager'], 'integer'],
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
            // Таблица 1. Количество отправлений в разрезе способов доставки.
            'table1_name' => 'Способ доставки',
            'table1_count' => 'Кол-во',

            // Таблица 2. Среднее время доставки в разрезе способов доставки.
            'table2_name' => 'Способ доставки',
            'table2_value' => 'Время',

            // Таблица 3. Среднее время на операции.
            'table3_name' => 'Операция',
            'table3_value' => 'Выполняется в среднем за',

            // ручные отправки:
            // Таблица 3. Количество отправлений в разрезе контрагентов.
            'table22_name' => 'Контрагент',
            'table22_count' => 'Кол-во',
            'table22_rejects_count' => 'Отказов',

            // Таблица 3. Количество отправлений в разрезе статусов.
            'table23_name' => 'Статус',
            'table23_count' => 'Кол-во',

            // Таблица 4. Среднее время на согласование по менеджерам.
            'table24_name' => 'Статус',
            'table24_value' => 'Время обработки',

            // для отбора
            'searchPeriodStart' => 'Начало периода',
            'searchPeriodEnd' => 'Конец периода',
            'searchManager' => 'Менеджер',
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
                    'table' . $tableNum . '_id' => $row['id'],
                    'table' . $tableNum . '_name' => $row[$columnName],
                    'table' . $tableNum . '_count' => 1,
                ];
            }
        }

        return $result;
    }

    /**
     * Преобразует массив из параметров, сворачивая его по полю $columnName, суммируя при этом количество пакетов
     * корреспонденции. Только для таблицы 2 ручных пакетов!
     * @param $array array массив пакетов корреспонденции
     * @param $tableNum string номер таблицы отчета
     * @param $columnName string наименование колонки, которая суммируется
     * @return array
     */
    public function collapseTableByFields($array, $tableNum, $columnName)
    {
        $result = [];

        foreach ($array as $index => $row) {
            $key = array_search($row[$columnName], array_column($result, 'table' . $tableNum . '_name'));
            if (false !== $key) {
                // если имя суммируемой колонки уже есть в таблице, увеличим количество
                $result[$key]['table' . $tableNum . '_count']++;
                $result[$key]['table' . $tableNum . '_rejects_count'] =+ intval($row['rejects_count']);
            }
            else {
                // если имени суммирумоей колонки нет в результирующем массиве, то просто добавляем его с единицей
                $result[] = [
                    'table' . $tableNum . '_id' => $row['id'],
                    'table' . $tableNum . '_name' => $row[$columnName],
                    'table' . $tableNum . '_count' => 1,
                    'table' . $tableNum . '_rejects_count' => 0,
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
            'pd_id',
            'table2_id' => 'pd_id',
            'table2_name' => 'post_delivery_kinds.name',
            'table2_value' => new Expression('AVG(`delivered_at` - `created_at`)'),
        ]);
        $query->joinWith(['pd'])->where([
            'and',
            ['is not', 'delivered_at', null],
            ['<>', 'state_id', ProjectsStates::STATE_НЕВОСТРЕБОВАНО],
        ])->groupBy('pd_id');
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
        $query = CorrespondencePackages::find()->where([
            'and',
            ['is not', 'delivered_at', null],
            ['<>', 'state_id', ProjectsStates::STATE_НЕВОСТРЕБОВАНО],
        ]);
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
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив пакетов корреспонденции
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable21($dataArray)
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
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив пакетов корреспонденции
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable22($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCorrespondenceAnalytics',
            'allModels' => $this->collapseTableByFields($dataArray, 22, 'ca_name'),
            'key' => 'table22_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table22_count' => SORT_DESC],
                'attributes' => [
                    'table22_id',
                    'table22_name',
                    'table22_count',
                    'table22_rejects_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив пакетов корреспонденции
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable23($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCorrespondenceAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 23, 'cpsName'),
            'key' => 'table23_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table23_count' => SORT_DESC],
                'attributes' => [
                    'table23_id',
                    'table23_name',
                    'table23_count',
                ],
            ],
        ]);
    }

    /**
     * Формирует таблицу 4.
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable24()
    {
        $query = CorrespondencePackages::find()->select([
            'manager_id',
            'table24_id' => 'manager_id',
            'table24_name' => 'managerProfile.name',
            'table24_value' => new Expression('AVG(`ready_at` - `created_at`)'),
        ]);
        $query->joinWith(['managerProfile'])->where('ready_at IS NOT NULL')->andWhere(['is_manual' => true])->groupBy('table24_id');
        $this->addConditionIfPeriodFilterIs($query);

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCorrespondenceAnalytics',
            'allModels' => $query->asArray()->all(),
            'key' => 'table24_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table24_value' => SORT_ASC],
                'attributes' => [
                    'table24_id',
                    'table24_name',
                    'table24_value',
                ],
            ],
        ]);
    }

    /**
     * Создает массив пакетов корреспонденции с такими полями:
     * correspondence_packages.id | pd_id | post_delivery_kinds.name
     * @param $params array массив параметров выборки (условия отбора)
     * @return array
     */
    public function search($params)
    {
        $tableName = CorrespondencePackages::tableName();
        $this->load($params);

        $query = CorrespondencePackages::find()->select([
            $tableName . '.id',
            $tableName . '.cps_id',
            'pd_id',
            'ca_id' => $tableName . '.fo_id_company',
            'ca_name' => $tableName . '.customer_name',
            'cpsName' => 'correspondence_packages_states.name',
            $tableName . '.rejects_count',
            'pdName' => 'post_delivery_kinds.name',
        ]);
        $query->joinWith(['cps', 'pd']);

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

        // дополним возможным условием по ручным пакетам
        $query->andFilterWhere(['is_manual' => $this->searchManual]);

        // дополним возможным условием по ответственному менеджеру
        $query->andFilterWhere(['manager_id' => $this->searchManager]);

        return $query->asArray()->all();
    }
}
