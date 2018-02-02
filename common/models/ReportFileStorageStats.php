<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * ReportFileStorageStats - это набор отчетов для анализа файлового хранилища.
 */
class ReportFileStorageStats extends Model
{
    const CAPTION_FOR_TABLE1 = 'Таблица 1. Статистика в разрезе ответственных.';
    const CAPTION_FOR_TABLE2 = 'Таблица 2. Статистика в разрезе контрагентов.';

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
            // Таблица 1. Статистика в разрезе ответственных.
            'table1_name' => 'Ответственный',
            'table1_views' => 'Просмотров',
            'table1_downloads' => 'Скачиваний',
            'table1_uploads' => 'Загрузок',

            // Таблица 2. Статистика в разрезе контрагентов.
            'table2_name' => 'Контрагент',
            'table2_views' => 'Просмотров',
            'table2_downloads' => 'Скачиваний',
            'table2_uploads' => 'Загрузок',

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
     * Соединяет массив просмотров и массив скачиваний в один и возвращает его.
     * @param $views array массив с просмотрами
     * @param $downloads array массив со скачиваниями
     * @param $uploads array массив с загрузками на сервер
     * @param $tableNum string номер таблицы отчета
     * @param $columnName string наименование колонки, где хранится показатель
     * @param $sortColumnName string наименование колонки, по которой осуществляется сортировка
     * @return array
     */
    public function mergeViewsAndDownloads($views, $downloads, $uploads, $tableNum, $columnName, $sortColumnName)
    {
        $result = $views;

        foreach ($downloads as $index => $row) {
            $key = array_search($row[$columnName], array_column($result, $columnName));
            if (false !== $key) {
                // если показатель уже есть в таблице, проставим напротив него также количество скачиваний
                $result[$key]['table' . $tableNum . '_downloads'] = $row['table' . $tableNum . '_downloads'];
            }
            else
                // если имени суммирумоей колонки нет в результирующем массиве, то просто добавляем его
                $result[] = [
                    'table' . $tableNum . '_name' => $row[$columnName],
                    'table' . $tableNum . '_downloads' => $row['table' . $tableNum . '_downloads'],
                ];
        }

        foreach ($uploads as $index => $row) {
            $key = array_search($row[$columnName], array_column($result, $columnName));
            if (false !== $key)
                // если показатель уже есть в таблице, проставим напротив него также количество скачиваний
                $result[$key]['table' . $tableNum . '_uploads'] = $row['table' . $tableNum . '_uploads'];
            else
                // если имени суммирумоей колонки нет в результирующем массиве, то просто добавляем его
                $result[] = [
                    'table' . $tableNum . '_name' => $row[$columnName],
                    'table' . $tableNum . '_uploads' => $row['table' . $tableNum . '_uploads'],
                ];
        }

        // сортируем результирующую таблицу
        ArrayHelper::multisort($result, $sortColumnName);

        return $result;
    }

    /**
     * Формирует таблицу 1.
     * @param $params array массив параметров выборки (условия отбора)
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable1($params)
    {
        $this->load($params);

        // выборка просмотров
        $query = FileStorageStats::find()->select([
            'table1_id' => 'file_storage_stats.created_by',
            'table1_name' => 'profile.name',
            'table1_views' => 'COUNT(`file_storage_stats`.`id`)',
        ])
            ->where(['type' => FileStorageStats::STAT_TYPE_ПРОСМОТР])
            ->joinWith(['createdByProfile', 'fs'])
            ->groupBy('profile.name');
        // уточняется запрос условием отбора за период, если он применяется
        $this->addConditionIfPeriodFilterIs($query);
        $views = $query->asArray()->all();

        // выборка скачиваний
        $query = FileStorageStats::find()->select([
            'table1_id' => 'file_storage_stats.created_by',
            'table1_name' => 'profile.name',
            'table1_downloads' => 'COUNT(`file_storage_stats`.`id`)',
        ])
            ->where(['type' => FileStorageStats::STAT_TYPE_СКАЧИВАНИЕ])
            ->joinWith(['createdByProfile', 'fs'])
            ->groupBy('profile.name');
        // уточняется запрос условием отбора за период, если он применяется
        $this->addConditionIfPeriodFilterIs($query);
        $downloads = $query->asArray()->all();

        // выборка загрузок
        $query = FileStorageStats::find()->select([
            'table1_id' => 'file_storage_stats.created_by',
            'table1_name' => 'profile.name',
            'table1_uploads' => 'COUNT(`file_storage_stats`.`id`)',
        ])
            ->where(['type' => FileStorageStats::STAT_TYPE_ЗАГРУЗКА_НА_СЕРВЕР])
            ->joinWith(['createdByProfile', 'fs'])
            ->groupBy('profile.name');
        // уточняется запрос условием отбора за период, если он применяется
        $this->addConditionIfPeriodFilterIs($query);
        $uploads = $query->asArray()->all();

        // в цикле соединяем таблицы
        $result = $this->mergeViewsAndDownloads($views, $downloads, $uploads, 1, 'table1_name', 'table1_name');

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportFileStorageStats',
            'allModels' => $result,
            'key' => 'table1_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table1_name' => SORT_ASC],
                'attributes' => [
                    'table1_id',
                    'table1_name',
                    'table1_views',
                    'table1_downloads',
                    'table1_uploads',
                ],
            ],
        ]);
    }

    /**
     * Формирует таблицу 2.
     * @param $params array массив параметров выборки (условия отбора)
     * @return ArrayDataProvider
     */
    public function makeDataProviderForTable2($params)
    {
        $this->load($params);

        // выборка просмотров
        $query = FileStorageStats::find()->select([
            'table2_id' => 'file_storage.ca_id',
            'table2_name' => 'file_storage.ca_name',
            'table2_views' => 'COUNT(`file_storage_stats`.`id`)',
        ])
            ->where(['type' => FileStorageStats::STAT_TYPE_ПРОСМОТР])
            ->joinWith(['createdByProfile', 'fs'])
            ->groupBy('file_storage.ca_name');
        // уточняется запрос условием отбора за период, если он применяется
        $this->addConditionIfPeriodFilterIs($query);
        $views = $query->asArray()->all();

        // выборка скачиваний
        $query = FileStorageStats::find()->select([
            'table2_id' => 'file_storage.ca_id',
            'table2_name' => 'file_storage.ca_name',
            'table2_downloads' => 'COUNT(`file_storage_stats`.`id`)',
        ])
            ->where(['type' => FileStorageStats::STAT_TYPE_СКАЧИВАНИЕ])
            ->joinWith(['createdByProfile', 'fs'])
            ->groupBy('file_storage.ca_name');
        // уточняется запрос условием отбора за период, если он применяется
        $this->addConditionIfPeriodFilterIs($query);
        $downloads = $query->asArray()->all();

        // выборка загрузок
        $query = FileStorageStats::find()->select([
            'table2_id' => 'file_storage.ca_id',
            'table2_name' => 'file_storage.ca_name',
            'table2_uploads' => 'COUNT(`file_storage_stats`.`id`)',
        ])
            ->where(['type' => FileStorageStats::STAT_TYPE_ЗАГРУЗКА_НА_СЕРВЕР])
            ->joinWith(['createdByProfile', 'fs'])
            ->groupBy('file_storage.ca_name');
        // уточняется запрос условием отбора за период, если он применяется
        $this->addConditionIfPeriodFilterIs($query);
        $uploads = $query->asArray()->all();

        // в цикле соединяем таблицы
        $result = $this->mergeViewsAndDownloads($views, $downloads, $uploads, 2, 'table2_name', 'table2_name');

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReportFileStorageStats',
            'allModels' => $result,
            'key' => 'table2_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['table2_name' => SORT_ASC],
                'attributes' => [
                    'table2_id',
                    'table2_name',
                    'table2_views',
                    'table2_downloads',
                    'table2_uploads',
                ],
            ],
        ]);
    }
}
