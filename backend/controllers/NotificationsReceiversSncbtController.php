<?php

namespace backend\controllers;

use Yii;
use common\models\NotifReceiversStatesNotChangedByTime;
use common\models\NotifReceiversStatesNotChangedByTimeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Справочник для хранения получателей уведомлений по E-mail сведений о проектах, статусы которых не меняются в течение заданного администратором времени.
 */
class NotificationsReceiversSncbtController extends Controller
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
     * Lists all NotifReceiversStatesNotChangedByTime models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NotifReceiversStatesNotChangedByTimeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new NotifReceiversStatesNotChangedByTime model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new NotifReceiversStatesNotChangedByTime();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/notifications-receivers-sncbt']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing NotifReceiversStatesNotChangedByTime model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $time = 0;
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(['/notifications-receivers-sncbt']);
        }
        else {
            $time = $model->time;
            $model->time /= 60;
            $model->periodicity = NotifReceiversStatesNotChangedByTime::PERIOD_MINUTE;
        }

        return $this->render('update', [
            'model' => $model,
            'time' => $time,
        ]);
    }

    /**
     * Deletes an existing NotifReceiversStatesNotChangedByTime model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/notifications-receivers-sncbt']);
    }

    /**
     * Finds the NotifReceiversStatesNotChangedByTime model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NotifReceiversStatesNotChangedByTime the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NotifReceiversStatesNotChangedByTime::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
