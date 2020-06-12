<?php

namespace backend\controllers;

use Yii;
use common\models\TendersLossReasons;
use common\models\TendersLossReasonsSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TendersLossReasonsController implements the CRUD actions for TendersLossReasons model.
 */
class TendersLossReasonsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = 'tenders-loss-reasons';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Возможные причины проигрышей по тендерам';

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
     * Lists all TendersLossReasons models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TendersLossReasonsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new TendersLossReasons model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TendersLossReasons();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::URL_ROOT_AS_ARRAY);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TendersLossReasons model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::URL_ROOT_AS_ARRAY);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TendersLossReasons model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->checkIfUsed()) {
            return $this->render('/common/cannot_delete', [
                'details' => [
                    'breadcrumbs' => ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY],
                    'modelRep' => $model->name,
                    'buttonCaption' => self::ROOT_LABEL,
                    'buttonUrl' => self::URL_ROOT_AS_ARRAY,
                    'action1' => 'удалить',
                    'action2' => 'удален',
                ],
            ]);
        }

        $model->delete();

        return $this->redirect(self::URL_ROOT_AS_ARRAY);
    }

    /**
     * Finds the TendersLossReasons model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TendersLossReasons the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TendersLossReasons::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }
}
