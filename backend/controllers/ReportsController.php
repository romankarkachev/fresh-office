<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use common\models\Report1;

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
                        'actions' => ['eins'],
                        'allow' => true,
                        'roles' => ['root', 'role_report1'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
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
    public function actionEins()
    {
        $searchModel = new Report1();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/reports/eins']);
                return false;
            }

            $model = new Report1();
            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Отчет по клиентам (сформирован '.date('Y-m-d').').xlsx',
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
                        'attribute' => 'reliable',
                        'header' => $model->attributeLabels()['reliable'],
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

            return $this->render('eins', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }
}
