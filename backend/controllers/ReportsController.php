<?php

namespace backend\controllers;

use common\models\ReportCaDuplicates;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use common\models\ReportTurnover;
use common\models\ReportNofinances;
use common\models\DirectMSSQLQueries;

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
                        'roles' => ['root', 'role_report1'],
                    ],
                    [
                        'actions' => ['nofinances', 'ca-duplicates'],
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

            $model = new ReportTurnover();
            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Отчет по клиентам (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'id',
                        'header' => $model->attributeLabels()['id'],
                    ],
                    [
                        'attribute' => 'name',
                        'header' => $model->attributeLabels()['name'],
                    ],
                    [
                        'attribute' => 'responsible',
                        'header' => $model->attributeLabels()['responsible'],
                    ],
                    [
                        'attribute' => 'turnover',
                        'header' => $model->attributeLabels()['turnover'],
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

            $model = new ReportCaDuplicates();
            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Отчет по дубликатам клиентам (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'id',
                        'header' => $model->attributeLabels()['id'],
                    ],
                    [
                        'attribute' => 'name',
                        'header' => $model->attributeLabels()['name'],
                    ],
                    [
                        'attribute' => 'parameter',
                        'header' => $model->attributeLabels()['parameter'],
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
}
