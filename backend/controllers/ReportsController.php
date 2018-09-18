<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use common\models\DirectMSSQLQueries;
use common\models\TransportRequests;
use common\models\CorrespondencePackages;
use common\models\ReportTurnover;
use common\models\ReportNofinances;
use common\models\ReportAnalytics;
use common\models\ReportCaDuplicates;
use common\models\ReportNoTransportHasProjects;
use common\models\ReportEmptycustomers;
use common\models\ReportTRAnalytics;
use common\models\ReportCorrespondenceAnalytics;
use common\models\ReportFileStorageStats;
use common\models\ReportPbxAnalytics;

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
                        'roles' => ['root', 'sales_department_head', 'dpc_head'],
                    ],
                    [
                        'actions' => ['analytics', 'no-transport-has-projects'],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_head'],
                    ],
                    [
                        'actions' => ['ca-duplicates'],
                        'allow' => true,
                        'roles' => ['root', 'dpc_head', 'sales_department_head'],
                    ],
                    [
                        'actions' => [
                            'emptycustomers', 'nofinances', 'no-transport-has-projects', 'analytics', 'tr-analytics',
                            'correspondence-analytics', 'correspondence-manual-analytics', 'file-storage-stats',
                            'pbx-analytics', 'pbx-calls-has-tasks-assigned',
                        ],
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
                    ],
                    [
                        'attribute' => 'first_payment',
                        'header' => $searchModel->attributeLabels()['first_payment'],
                        'format' => 'date',
                    ],
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
     * Отображает отчет по клиентам без любых данных: без контактных лиц, без финансов и без проектов.
     * Если тип запроса POST, то выполняется обработка, отчет не выводится.
     */
    public function actionEmptycustomers()
    {
        if (Yii::$app->request->isPost) {
            // если пришел POST-запрос, значит выполняется обработка
            Yii::$app->response->format = Response::FORMAT_JSON;

            $ca_ids = Yii::$app->request->post('ca_ids');
            // все параметры обязательны
            if ($ca_ids == null) return false;

            return DirectMSSQLQueries::deleteCustomers($ca_ids);
        }

        $searchModel = new ReportEmptycustomers();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/reports/emptycustomers']);
                return false;
            }

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Пустые клиенты (сформирован '.date('Y-m-d в H i').').xlsx',
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

            return $this->render('emptycustomers', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
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

    /**
     * Отображает отчет с анализом запросов на транспорт.
     * @return mixed
     */
    public function actionTrAnalytics()
    {
        $avgFinish = intval(TransportRequests::find()->where('finished_at IS NOT NULL')->average('finished_at - created_at'));
        $avgComputedFinish = intval(TransportRequests::find()->where('computed_finished_at IS NOT NULL')->average('computed_finished_at - created_at'));

        $searchModel = new ReportTRAnalytics();
        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        $trArray = $searchModel->search(Yii::$app->request->queryParams);
        $wasteArray = $searchModel->searchWaste(Yii::$app->request->queryParams);
        $transportArray = $searchModel->searchTransport(Yii::$app->request->queryParams);

        // Таблица 1. Запросы в разрезе статусов.
        $dpTable1 = $searchModel->makeDataProviderForTable1($trArray);

        // Таблица 2. Запросы в разрезе регионов.
        $dpTable2 = $searchModel->makeDataProviderForTable2($trArray);

        // Таблица 3. Запросы в разрезе менеджеров.
        $dpTable3 = $searchModel->makeDataProviderForTable3($trArray);

        // Таблица 4. Запросы в разрезе периодичности обращения.
        $dpTable4 = $searchModel->makeDataProviderForTable4($trArray);

        // Таблица 5. Запросы в разрезе отходов.
        $dpTable5 = $searchModel->makeDataProviderForTable5($wasteArray);

        // Таблица 6. Запросы в разрезе типов транспорта.
        $dpTable6 = $searchModel->makeDataProviderForTable6($transportArray);

        return $this->render('tranalytics', [
            'searchModel' => $searchModel,
            'searchApplied' => $searchApplied,
            'avgFinish' => $avgFinish,
            'avgComputedFinish' => $avgComputedFinish,
            'totalCount' => count($trArray),
            'dpTable1' => $dpTable1,
            'dpTable2' => $dpTable2,
            'dpTable3' => $dpTable3,
            'dpTable4' => $dpTable4,
            'dpTable5' => $dpTable5,
            'dpTable6' => $dpTable6,
        ]);
    }

    /**
     * Отображает отчет с анализом корреспонденции (пакетов документов).
     * @return mixed
     */
    public function actionCorrespondenceAnalytics()
    {
        $searchModel = new ReportCorrespondenceAnalytics();
        $cArray = $searchModel->search(Yii::$app->request->queryParams);

        // Таблица 1. Количество отправлений в разрезе способов доставки.
        $dpTable1 = $searchModel->makeDataProviderForTable1($cArray);

        // Таблица 2. Среднее время доставки в разрезе способов доставки.
        $dpTable2 = $searchModel->makeDataProviderForTable2();

        // Таблица 3. Среднее время на операции.
        $dpTable3 = $searchModel->makeDataProviderForTable3();

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('correspondenceanalytics', [
            'searchModel' => $searchModel,
            'searchApplied' => $searchApplied,
            'totalCount' => count($cArray),
            'dpTable1' => $dpTable1,
            'dpTable2' => $dpTable2,
            'dpTable3' => $dpTable3,
        ]);
    }

    /**
     * Отображает отчет с анализом корреспонденции, созданным вручную.
     * @return mixed
     */
    public function actionCorrespondenceManualAnalytics()
    {
        $avgFinish = intval(CorrespondencePackages::find()->where('ready_at IS NOT NULL')->average('ready_at - created_at'));

        $searchModel = new ReportCorrespondenceAnalytics();
        $cArray = $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, [
            $searchModel->formName() => [
                'searchManual' => true,
            ]
        ]));

        // Таблица 1. Количество отправлений в разрезе способов доставки.
        $dpTable1 = $searchModel->makeDataProviderForTable21($cArray);

        // Таблица 2. Количество отправлений в разрезе контрагентов.
        $dpTable2 = $searchModel->makeDataProviderForTable22($cArray);

        // Таблица 3. Количество отправлений в разрезе статусов.
        $dpTable3 = $searchModel->makeDataProviderForTable23($cArray);

        // Таблица 4. Среднее время на согласование по менеджерам.
        $dpTable4 = $searchModel->makeDataProviderForTable24();

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('correspondencemanualanalytics', [
            'searchModel' => $searchModel,
            'searchApplied' => $searchApplied,
            'avgFinish' => $avgFinish,
            'totalCount' => count($cArray),
            'dpTable1' => $dpTable1,
            'dpTable2' => $dpTable2,
            'dpTable3' => $dpTable3,
            'dpTable4' => $dpTable4,
        ]);
    }

    /**
     * Отображает отчет со статистикой по обращениям к файловому хранилищу.
     * @return mixed
     */
    public function actionFileStorageStats()
    {
        $searchModel = new ReportFileStorageStats();

        // Таблица 1. Статистика в разрезе ответственных.
        $dpTable1 = $searchModel->makeDataProviderForTable1(Yii::$app->request->queryParams);

        // Таблица 2. Статистика в разрезе контрагентов.
        $dpTable2 = $searchModel->makeDataProviderForTable2(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('fsstats', [
            'searchModel' => $searchModel,
            'searchApplied' => $searchApplied,
            'dpTable1' => $dpTable1,
            'dpTable2' => $dpTable2,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionPbxAnalytics()
    {
        $searchModel = new ReportPbxAnalytics();

        // Таблица 1. Общие показатели.
        $dpTable1 = $searchModel->makeDataProviderForTable1(Yii::$app->request->queryParams);

        // Таблица 2, 3, 4, 5: Распределение по регионам, Распределение по сотрудникам, Распределение по сайтам, Потерянные звонки
        list($dpTable2, $dpTable3, $dpTable4, $dpTable5) = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('pbxcalls', [
            'searchModel' => $searchModel,
            'searchApplied' => $searchApplied,
            'dpTable1' => $dpTable1,
            'dpTable2' => $dpTable2,
            'dpTable3' => $dpTable3,
            'dpTable4' => $dpTable4,
            'dpTable5' => $dpTable5,
        ]);
    }

    /**
     * Отображает отчет по задачам и незавершенным проектам контрагентов, которые звонили сегодня и были идентифицированы.
        SELECT COMPANY.[ID_COMPANY], [COMPANY_NAME], (
            SELECT COUNT(ID_LIST_PROJECT_COMPANY) FROM CBaseCRM_Fresh_7x.dbo.LIST_PROJECT_COMPANY
            WHERE COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY AND ID_PRIZNAK_PROJECT <> 25
        ), (
            SELECT COUNT(ID_CONTACT) FROM CBaseCRM_Fresh_7x.dbo.LIST_CONTACT_COMPANY
            WHERE COMPANY.ID_COMPANY = LIST_CONTACT_COMPANY.ID_COMPANY AND ID_PRIZNAK_CONTACT <> 2
        )
        FROM [CBaseCRM_Fresh_7x].[dbo].[COMPANY]
        WHERE ID_COMPANY IN (6757, 13592, 8243, 11417, 6989)
        ORDER BY COMPANY_NAME
     *
     * @return mixed
     */
    public function actionPbxCallsHasTasksAssigned()
    {
        $searchModel = new ReportPbxAnalytics();
        $dataProvider = $searchModel->searchCurrentTasks(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/reports/pbx-calls-has-tasks-assigned']);
                return false;
            }

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Незавершенные проекты и наличие задач по контрагентам на дату ' . Yii::$app->formatter->asDate($searchModel->searchPeriodEnd, 'php:d F Y') . ' (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'pbxht_id',
                        'header' => $searchModel->attributeLabels()['pbxht_id'],
                    ],
                    [
                        'attribute' => 'pbxht_name',
                        'header' => $searchModel->attributeLabels()['pbxht_name'],
                    ],
                    [
                        'attribute' => 'pbxht_managerName',
                        'header' => $searchModel->attributeLabels()['pbxht_managerName'],
                    ],
                    [
                        'attribute' => 'pbxht_projectsInProgressCount',
                        'header' => $searchModel->attributeLabels()['pbxht_projectsInProgressCount'],
                    ],
                    [
                        'attribute' => 'pbxht_tasksCount',
                        'header' => $searchModel->attributeLabels()['pbxht_tasksCount'],
                    ],
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            return $this->render('pbxhastasks', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }
}
