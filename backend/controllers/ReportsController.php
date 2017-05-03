<?php

namespace backend\controllers;

use common\models\Appeals;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use common\models\DirectMSSQLQueries;
use common\models\ReportTurnover;
use common\models\ReportNofinances;
use common\models\ReportAnalytics;
use common\models\ReportCaDuplicates;
use common\models\ReportNoTransportHasProjects;

/**
 * Reports controller
 */
class ReportsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['turnover'],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_head'],
                    ],
                    [
                        'actions' => ['nofinances', 'ca-duplicates', 'no-transport-has-projects', 'analytics'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Отображает отчет по клиентам.
     * ID_SUB_PRIZNAK_MANY - признак оплаты (Утилизация, Транспорт, Учебный центр...)
     * ID_NAPR - направление движения (1 - приход, 2 - расход)
     * @return string
     */
    public function actionTurnover()
    {
        $searchModel = new ReportTurnover();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/reports/turnover']);
                return false;
            }

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Отчет по клиентам (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'id',
                        'header' => $searchModel->attributeLabels()['id'],
                    ],
                    [
                        'attribute' => 'name',
                        'header' => $searchModel->attributeLabels()['name'],
                    ],
                    [
                        'attribute' => 'responsible',
                        'header' => $searchModel->attributeLabels()['responsible'],
                    ],
                    [
                        'attribute' => 'turnover',
                        'header' => $searchModel->attributeLabels()['turnover'],
                    ]
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&'.Yii::$app->request->queryString;

            return $this->render('turnover', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }

    /**
     * Отображает отчет по клиентам без оборотов.
     * Если тип запроса POST, то выполняется обработка, отчет не выводится.
     */
    public function actionNofinances()
    {
        if (Yii::$app->request->isPost) {
            // если пришел POST-запрос, значит выполняется обработка
            Yii::$app->response->format = Response::FORMAT_JSON;

            $ca_ids = Yii::$app->request->post('ca_ids');
            $manager_id = Yii::$app->request->post('manager_id');
            // все параметры обязательны
            if ($ca_ids == null || $manager_id == null) return false;

            return DirectMSSQLQueries::changeResponsible($ca_ids, $manager_id);
        }

        $searchModel = new ReportNofinances();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('nofinances', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Отображает отчет по дубликатам в контрагентах.
     * Поля для поиска: наименование, номер телефона, email.
     */
    public function actionCaDuplicates()
    {
        $searchModel = new ReportCaDuplicates();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/reports/ca-duplicates']);
                return false;
            }

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Отчет по дубликатам клиентов (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'name',
                        'header' => $searchModel->attributeLabels()['name'],
                    ],
                    [
                        'attribute' => 'parameter',
                        'header' => $searchModel->attributeLabels()['parameter'],
                    ],
                    [
                        'attribute' => 'owners',
                        'header' => $searchModel->attributeLabels()['owners'],
                    ],
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            return $this->render('caduplicates', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }

    /**
     * Отображает отчет по контрагентам, у которых есть записи в финансах с признаком утилизация, но нет записей
     * с признаком транспорт. Также у контрагента должны быть проекты.
     * Поля для поиска: начало и конец периода (для финансов).
     * Поля для вывода: id, наименование, ответственный контрагента.
     */
    public function actionNoTransportHasProjects()
    {
        $searchModel = new ReportNoTransportHasProjects();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/reports/ca-duplicates']);
                return false;
            }

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Отчет по утилизации без транспорта (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'id',
                        'header' => $searchModel->attributeLabels()['id'],
                    ],
                    [
                        'attribute' => 'name',
                        'header' => $searchModel->attributeLabels()['name'],
                    ],
                    [
                        'attribute' => 'responsible',
                        'header' => $searchModel->attributeLabels()['responsible'],
                    ],
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            return $this->render('notransporthasprojects', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }

    /**
     * Отображает отчет с анализом обращений (по ответственным, по источникам обращения и т.д.).
     * Поля для поиска: раздел учета, начало и конец периода (для выборки обращений).
     */
    public function actionAnalytics()
    {
        $searchModel = new ReportAnalytics();
        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        // если нет отбора за период, то отчет не показываем совсем
        if (count(Yii::$app->request->queryParams) == 0) {
            // можно и вернуть значение по-умолчанию:
            //$searchModel->searchAccountSection = Appeals::РАЗДЕЛ_УЧЕТА_УТИЛИЗАЦИЯ;
            return $this->render('_nodata_analytics', [
                'searchModel' => $searchModel,
            ]);
        }

        $appeals_array = $searchModel->search(Yii::$app->request->queryParams);
        $totalAppealsPeriod = count($appeals_array);

        // Таблица 1. Всего обращений по ответственным за период.
        $dpTable1 = $searchModel->makeDataProviderForTable1($appeals_array);

        // Таблица 2. Всего обращений по источникам за период.
        $dpTable2 = $searchModel->makeDataProviderForTable2($appeals_array);

        // для остальных таблиц выборка всех обращений
        unset($appeals_array);
        $appeals_array = $searchModel->search(Yii::$app->request->queryParams, ReportAnalytics::MODE_ALL);
        $totalAppeals = count($appeals_array);

        // Таблица 3. Всего обращений по их статусам.
        $dpTable3 = $searchModel->makeDataProviderForTable3($appeals_array);

        // Таблица 4. Всего обращений по статусам клиентов.
        $dpTable4 = $searchModel->makeDataProviderForTable4($appeals_array);

        // Таблица 5. Обращения по ответственным в разрезе статусов обращений.
        $columns5 = [];
        $dpTable5 = $searchModel->makeDataProviderForTable(5, $appeals_array, 'responsible_id', 'responsible_name', $columns5);

        // Таблица 6. Обращения по источникам в разрезе стасусов обращений.
        $columns6 = [];
        $dpTable6 = $searchModel->makeDataProviderForTable(6, $appeals_array, 'as_id', 'as_name', $columns6);

        return $this->render('analytics', [
            'searchModel' => $searchModel,
            'searchApplied' => $searchApplied,
            'totalAppealsPeriod' => $totalAppealsPeriod,
            'totalAppeals' => $totalAppeals,
            'dpTable1' => $dpTable1,
            'dpTable2' => $dpTable2,
            'dpTable3' => $dpTable3,
            'dpTable4' => $dpTable4,
            'dpTable5' => $dpTable5,
            'columns5' => $columns5,
            'dpTable6' => $dpTable6,
            'columns6' => $columns6,
        ]);
    }
}
