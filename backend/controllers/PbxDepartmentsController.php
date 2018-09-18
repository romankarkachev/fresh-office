<?php

namespace backend\controllers;

use Yii;
use common\models\pbxDepartments;
use common\models\pbxDepartmentsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * PbxDepartmentsController implements the CRUD actions for pbxDepartment model.
 */
class PbxDepartmentsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'pbx-departments';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Отделы';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Отделы компании в Мини-АТС';

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
     * Lists all pbxDepartment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new pbxDepartmentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new pbxDepartment model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new pbxDepartments();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing pbxDepartment model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing pbxDepartment model.
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

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the pbxDepartment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return pbxDepartments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = pbxDepartments::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
