<?php

namespace backend\controllers;

use Yii;
use common\models\pbxInternalPhoneNumber;
use common\models\pbxInternalPhoneNumberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * PbxInternalPhoneNumbersController implements the CRUD actions for pbxInternalPhoneNumber model.
 */
class PbxInternalPhoneNumbersController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'pbx-internal-phone-numbers';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Внутренние номера';

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
     * Lists all pbxInternalPhoneNumber models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new pbxInternalPhoneNumberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new pbxInternalPhoneNumber model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new pbxInternalPhoneNumber();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(self::ROOT_URL_AS_ARRAY);
        } else {
            $key = PbxCallsController::SESSION_PARAM_NAME_CREATE_INTERNAL_NUMBER . Yii::$app->user->id;
            $sessionParamValue = Yii::$app->session->get($key);
            if (!empty($sessionParamValue)) {
                $model->phone_number = $sessionParamValue;
                Yii::$app->session->remove($key);
            }
            unset($key);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing pbxInternalPhoneNumber model.
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
     * Deletes an existing pbxInternalPhoneNumber model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the pbxInternalPhoneNumber model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return pbxInternalPhoneNumber the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = pbxInternalPhoneNumber::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
