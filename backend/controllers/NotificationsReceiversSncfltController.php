<?php

namespace backend\controllers;

use Yii;
use common\models\NotifReceiversStatesNotChangedTodayForALongTime;
use common\models\NotifReceiversStatesNotChangedTodayForALongTimeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * В справочнике задаются получатели E-mail-уведомлений о том, что статусы проектов не меняются более 4 часов.
 */
class NotificationsReceiversSncfltController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['root'],
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
     * Lists all NotifReceiversStatesNotChangedTodayForALongTime models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NotifReceiversStatesNotChangedTodayForALongTimeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new NotifReceiversStatesNotChangedTodayForALongTime model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new NotifReceiversStatesNotChangedTodayForALongTime();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/notifications-receivers-sncflt']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing NotifReceiversStatesNotChangedTodayForALongTime model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/notifications-receivers-sncflt']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing NotifReceiversStatesNotChangedTodayForALongTime model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/notifications-receivers-sncflt']);
    }

    /**
     * Finds the NotifReceiversStatesNotChangedTodayForALongTime model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NotifReceiversStatesNotChangedTodayForALongTime the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NotifReceiversStatesNotChangedTodayForALongTime::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
