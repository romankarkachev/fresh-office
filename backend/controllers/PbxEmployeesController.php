<?php

namespace backend\controllers;

use Yii;
use common\models\pbxEmployees;
use common\models\pbxEmployeesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * PbxEmployeesController implements the CRUD actions for pbxEmployee model.
 */
class PbxEmployeesController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'pbx-employees';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Операторы';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Операторы Мини-АТС';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

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
     * Lists all pbxEmployee models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new pbxEmployeesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new pbxEmployee model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new pbxEmployees();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/pbx-employees']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing pbxEmployee model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/pbx-employees']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing pbxEmployee model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->checkIfUsed())
            return $this->render('/common/cannot_delete', [
                'details' => [
                    'breadcrumbs' => ['label' => self::MAIN_MENU_LABEL, 'url' => self::ROOT_URL_AS_ARRAY],
                    'modelRep' => $model->name,
                    'buttonCaption' => self::MAIN_MENU_LABEL,
                    'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                ],
            ]);

        $model->delete();

        return $this->redirect(['/pbx-employees']);
    }

    /**
     * Finds the pbxEmployee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return pbxEmployees the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = pbxEmployees::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
