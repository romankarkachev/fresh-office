<?php

namespace ferryman\controllers;

use Yii;
use common\models\DirectMSSQLQueries;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\Ferrymen;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

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
                'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
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
        if ($ferryman == null)
            $dataProvider = new ActiveDataProvider(['query' => foProjects::find()->where('1 <> 1')]);
        else
            $dataProvider = $searchModel->search(ArrayHelper::merge([
                'route' => 'freights',
                $searchModel->formName() => [
                    'oplata' => $ferryman->name_crm,
                ],
            ], Yii::$app->request->queryParams));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
