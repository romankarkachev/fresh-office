<?php

namespace backend\controllers;

use common\models\NotifReceiversStatesNotChangedByTime;
use Yii;
use common\models\OutdatedObjectsReceivers;
use common\models\OutdatedObjectsReceiversSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OutdatedObjectsReceiversController implements the CRUD actions for OutdatedObjectsReceivers model.
 */
class OutdatedObjectsReceiversController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = 'outdated-objects-receivers';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Получатели уведомлений о просроченных объектах';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all OutdatedObjectsReceivers models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OutdatedObjectsReceiversSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new OutdatedObjectsReceivers model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new OutdatedObjectsReceivers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::URL_ROOT_AS_ARRAY);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing OutdatedObjectsReceivers model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $time = 0;
        $model = $this->findModel($id);
        $params = ['model' => $model];

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(self::URL_ROOT_AS_ARRAY);
            }
        }
        else {
            $time = $model->time;
            $model->time /= 60;
            $model->periodicity = NotifReceiversStatesNotChangedByTime::PERIOD_MINUTE;
        }

        $params['time'] = $time;

        return $this->render('update', $params);
    }

    /**
     * Deletes an existing OutdatedObjectsReceivers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::URL_ROOT_AS_ARRAY);
    }

    /**
     * Finds the OutdatedObjectsReceivers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OutdatedObjectsReceivers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = OutdatedObjectsReceivers::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::$app->params['promptPageNotFound']);
    }
}
