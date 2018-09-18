<?php

namespace cemail\controllers;

use Yii;
use common\models\CEMailboxesCategories;
use common\models\CEMailboxesCategoriesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * CategoriesController implements the CRUD actions for CEMailboxesCategories model.
 */
class CategoriesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
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
     * Lists all CEMailboxesCategories models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CEMailboxesCategoriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CEMailboxesCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CEMailboxesCategories();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/categories']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CEMailboxesCategories model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/categories']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CEMailboxesCategories model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $details = [
            'details' => [
                'modelRep' => $model->name,
                'breadcrumbs' => ['label' => 'Категории', 'url' => ['/categories']],
                'buttonCaption' => 'Категории',
                'buttonUrl' => ['/categories'],
                'action1' => 'удалить',
                'action2' => 'удален',
            ],
        ];

        // запись не должна где-то быть использован
        if ($model->checkIfUsed()) return $this->render('@backend/views/common/cannot_delete', $details);

        $model->delete();

        return $this->redirect(['/categories']);
    }

    /**
     * Finds the CEMailboxesCategories model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CEMailboxesCategories the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CEMailboxesCategories::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
