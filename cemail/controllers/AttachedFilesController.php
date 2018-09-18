<?php

namespace cemail\controllers;

use Yii;
use common\models\CEAttachedFilesSearch;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * AttachedFilesController implements the CRUD actions for CEAttachedFiles model.
 */
class AttachedFilesController extends Controller
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
                        'actions' => [
                            'index',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CEAttachedFiles models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CEAttachedFilesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }
}
