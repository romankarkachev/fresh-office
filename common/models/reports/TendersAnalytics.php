<?php

namespace common\models\reports;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use common\models\Tenders;
use common\models\TendersStates;
use common\models\TendersLossReasons;
use common\models\TendersPlatforms;
use common\models\TendersLr;
use common\models\Organizations;

/**
 * Аналитика по тендерам.
 */
class TendersAnalytics extends Model
{
    /**
     * Названия таблиц
     */
    const CAPTION_FOR_TABLE1 = 'Таблица 1. Тендеры в работе за период по статусам.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Тендеры в финальных статусах за период.';
    const CAPTION_FOR_TABLE3 = 'Таблица 3. Тендеры за период по организациям.';
    const CAPTION_FOR_TABLE4 = 'Таблица 4. Тендеры за период по сложности.';
    const CAPTION_FOR_TABLE5 = 'Таблица 5. Проигранные за период тендеры в разрезе причин.';
    const CAPTION_FOR_TABLE6 = 'Таблица 6. Тендеры за период в разрезе законов.';
    const CAPTION_FOR_TABLE7 = 'Таблица 7. Тендеры за период по исполнителям.';
    const CAPTION_FOR_TABLE8 = 'Таблица 8. Тендеры за период в разрезе площадок.';
    const CAPTION_FOR_TABLE9 = 'Таблица 9. Тендеры за период по ответственным.';

    /**
     * @var string начало и конец периода для отбора по дате создания
     */
    public $searchCreatedAtStart;
    public $searchCreatedAtEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchCreatedAtStart', 'searchCreatedAtEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'searchCreatedAtStart' => 'Начало периода',
            'searchCreatedAtEnd' => 'Конец периода',

            // Таблицы 1 и 2. Закупки в текуших и финальных статусов.
            'tables1_2_id' => 'ID',
            'tables1_2_name' => 'Статус',
            'tables1_2_count' => 'Кол-во',

            // Таблица 3. Тендеры за период по организациям.
            'table3_id' => 'ID',
            'table3_name' => 'Организация',
            'table3_count' => 'Кол-во',

            // Таблица 4. Тендеры за период по сложности.
            'table4_id' => 'ID',
            'table4_name' => 'Сложность',
            'table4_count' => 'Кол-во',

            // Таблица 5. Проигранные за период тендеры в разрезе причин.
            'table5_name' => 'Причина',
            'table5_count' => 'Кол-во',

            // Таблица 6. Тендеры за период в разрезе законов.
            'table6_id' => 'ID',
            'table6_name' => '№ закона',
            'table6_count' => 'Кол-во',

            // Таблица 7. Тендеры за период по исполнителям.
            'table7_id' => 'ID',
            'table7_name' => 'Специалист',
            'table7_measure1' => 'I уровень',
            'table7_measure2' => 'II уровень',
            'table7_measure3' => 'III уровень',
            'table7_measurena' => 'н/о',
            'table7_count' => 'Кол-во',

            // Таблица 8. Тендеры за период в разрезе площадок.
            'table8_id' => 'ID',
            'table8_name' => 'Площадка',
            'table8_count' => 'Кол-во',

            // Таблица 9. Тендеры за период по ответственным.
            'table9_id' => 'ID',
            'table9_name' => 'Ответственный',
            'table9_count' => 'Кол-во',
        ];
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
            if (false !== $key) {
                // если имя суммируемой колонки уже есть в таблице, увеличим количество
                $result[$key]['table' . $tableNum . '_count']++;
            }
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

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return array
     */
    public function makeDataProvidersForTables1_2($dataArray)
    {
        $caUsual = [];
        $caFinished = [];
        foreach ($this->collapseTableByField($dataArray, 's1_2', 'stateName', 'state_id') as $index => $measure) {
            if (ArrayHelper::isIn($measure['tables1_2_id'], TendersStates::НАБОР_ФИНАЛЬНЫЕ_СТАТУСЫ)) {
                // тендер находится в финальном статусе, помещаем в один массив
                $caFinished[] = $measure;
            }
            else {
                // тендер еще в работе - помещаем в другой массив
                $caUsual[] = $measure;
            }
        }

        $result = [];
        foreach ([1 => $caUsual, 2 => $caFinished] as $index => $data) {
            $result[] = new ArrayDataProvider([
                'modelClass' => 'common\models\reports\TendersAnalytics',
                'allModels' => $data,
                'key' => 'tables1_2_id', // поле, которое заменяет primary key
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => ['tables1_2_count' => SORT_DESC],
                    'attributes' => [
                        'tables1_2_id',
                        'tables1_2_name',
                        'tables1_2_count',
                    ],
                ],
            ]);
        }

        return $result;
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable3($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 3, 'orgName', 'org_id'),
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
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable4($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 4, 'complexity', 'complexity'),
            'key' => 'table4_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table4_name' => SORT_ASC],
                'attributes' => [
                    'table4_id',
                    'table4_name',
                    'table4_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable5($dataArray)
    {
        $collapsedArray = $this->collapseTableByField($dataArray, 5, 'lossReasonName', 'lossReasonName');
        // очистим массив от пустых значений
        foreach ($collapsedArray as $index => $measure) {
            if (empty($measure['table5_name'])) {
                // пустое значение в данном случае может означать, что тендер не проигран
                unset($collapsedArray[$index]);
            }
        }

        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $collapsedArray,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table5_name' => SORT_ASC],
                'attributes' => [
                    'table5_name',
                    'table5_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable6($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 6, 'law_no', 'law_no'),
            'key' => 'table6_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table6_id' => SORT_ASC],
                'attributes' => [
                    'table6_id',
                    'table6_name',
                    'table6_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable7($dataArray)
    {
        $tablePrefix = 'table7_';
        $collapsedArray = [];
        foreach ($dataArray as $index => $row) {
            $key = array_search($row['responsibleProfileName'], array_column($collapsedArray, $tablePrefix . 'name'));
            if (false !== $key) {
                // если имя суммируемой колонки уже есть в таблице, увеличим количество
                $collapsedArray[$key][$tablePrefix . 'count']++;
                if (!empty($row['complexity'])) {
                    $collapsedArray[$key][$tablePrefix . 'measure' . $row['complexity']]++;
                }
                else {
                    $collapsedArray[$key][$tablePrefix . 'measurena']++;
                }
            }
            else {
                // если имени суммирумоей колонки нет в результирующем массиве, то просто добавляем его с единицей
                $item = [
                    $tablePrefix . 'id' => $row['responsible_id'],
                    $tablePrefix . 'name' => $row['responsibleProfileName'],
                    $tablePrefix . 'count' => 1,
                ];

                // добавляем колонки - уровни сложности
                for ($i = 1; $i <= 3; $i++) {
                    $item[$tablePrefix . 'measure' . $i] = null;

                    if (!empty($row['complexity'])) {
                        $item[$tablePrefix . 'measure' . $row['complexity']] = 1;
                    }
                }

                $item[$tablePrefix . 'measurena'] = null; // для тех тендеров, в которых по текущему специалисту не указана сложность

                $collapsedArray[] = $item;
                unset($item);
            }
        }

        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $collapsedArray,
            'key' => $tablePrefix . 'id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [$tablePrefix . 'count' => SORT_DESC],
                'attributes' => [
                    $tablePrefix . 'id',
                    $tablePrefix . 'name',
                    $tablePrefix . 'count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable8($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 8, 'tpName', 'tp_id'),
            'key' => 'table8_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table8_count' => SORT_DESC],
                'attributes' => [
                    'table8_id',
                    'table8_name',
                    'table8_count',
                ],
            ],
        ]);
    }

    /**
     * Возвращает dataProvider из массива в параметрах.
     * @param $dataArray array массив документов
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable9($dataArray)
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\reports\TendersAnalytics',
            'allModels' => $this->collapseTableByField($dataArray, 9, 'managerProfileName', 'manager_id'),
            'key' => 'table9_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table9_count' => SORT_DESC],
                'attributes' => [
                    'table9_id',
                    'table9_name',
                    'table9_count',
                ],
            ],
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return array
     */
    public function search($params)
    {
        $baseTableName = Tenders::tableName();
        $this->load($params);

        $query = Tenders::find()->select([
            'id' => $baseTableName . '.`id`',
            'law_no',
            'state_id',
            'org_id',
            'tp_id',
            'manager_id',
            'responsible_id',
            'complexity',
            'orgName' => Organizations::tableName() . '.name',
            'stateName' => TendersStates::tableName() . '.name',
            'tpName' => TendersPlatforms::tableName() . '.name',
            'managerProfileName' => 'managerProfile.name',
            'responsibleProfileName' => 'responsibleProfile.name',
            'lossReasonName' => TendersLr::find()->select(TendersLossReasons::tableName() . '.name')->where(['tender_id' => new Expression('`' . $baseTableName . '`.`id`')])->joinWith('lr'),
        ]);
        $query->joinWith(['state', 'org', 'tp', 'managerProfile', 'responsibleProfile']);

        // уточняется запрос
        // добавляется условие, если оно есть
        // только для вызова массива запросов на текущую дату
        if (!empty($this->searchCreatedAtStart) || !empty($this->searchCreatedAtEnd)) {
            // если указаны обе даты
            $query->andWhere(['between', 'created_at', strtotime($this->searchCreatedAtStart . ' 00:00:00'), strtotime($this->searchCreatedAtEnd . ' 23:59:59')]);
        } else if (!empty($this->searchCreatedAtStart) && empty($this->searchCreatedAtEnd)) {
            // если указано только начало периода
            $query->andWhere(['>=', 'created_at', strtotime($this->searchCreatedAtStart . ' 00:00:00')]);
        } else if (empty($this->searchPeriodStart) && !empty($this->searchCreatedAtEnd)) {
            // если указан только конец периода
            $query->andWhere(['<=', 'created_at', strtotime($this->searchCreatedAtEnd . ' 23:59:59')]);
        }

        return $query->asArray()->all();
    }
}
