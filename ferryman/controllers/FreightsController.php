<?php

namespace ferryman\controllers;

use Yii;
use common\models\DirectMSSQLQueries;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\Ferrymen;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use moonland\phpexcel\Excel;

/**
 * Работа с проектами Fresh Office.
 */
class FreightsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['direct-sql-counteragents-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'index',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'ferryman'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    //'some-action' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает список проектов.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new foProjectsSearch();
        $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
        // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
        if ($ferryman == null) {
            $dataProvider = new ActiveDataProvider(['query' => foProjects::find()->where('1 <> 1')]);
        }
        else {
            $dataProvider = $searchModel->search(ArrayHelper::merge([
                'route' => 'freights',
                $searchModel->formName() => [
                    'perevoz' => $ferryman->name_crm,
                    'state_id' => -1,
                ],
            ], Yii::$app->request->queryParams));
        }

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(Url::to(['/freights']));
                return false;
            }

            // именно для экспорта постраничный переход отключается, чтобы в файл выгружались все записи
            $dataProvider->pagination = false;
            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Рейсы (сформирован '.date('Y-m-d в H i').').xlsx',
                'asAttachment' => true,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'label' => '№ проекта',
                    ],
                    [
                        'attribute' => 'vivozdate',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'cost',
                        'label' => 'Стоимость',
                    ],
                    [
                        'attribute' => 'oplata',
                        'format' => 'date',
                    ],
                    'ttn',
                    'adres',
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'queryString' => $queryString,
            ]);
        }
    }

    /**
     * Функция выполняет поиск контрагента по наименованию, переданному в параметрах.
     * @param $q string
     * @return array
     */
    public function actionDirectSqlCounteragentsList($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['results' => DirectMSSQLQueries::fetchCounteragents($q)];
    }
}
