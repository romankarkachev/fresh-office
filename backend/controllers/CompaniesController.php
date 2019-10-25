<?php

namespace backend\controllers;

use Yii;
use common\models\Companies;
use common\models\CompaniesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CompaniesController implements the CRUD actions for Companies model.
 */
class CompaniesController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'companies';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Контрагенты';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для добавления записи
     */
    const URL_CREATE = 'create';
    const URL_CREATE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    /**
     * URL для интерактивного подбора контрагентов через поле ввода
     */
    const URL_CASTING = 'casting';
    const URL_CASTING_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CASTING];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [self::URL_CASTING],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [self::URL_CREATE],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'assistant', 'accountant', 'accountant_b', 'ecologist', 'ecologist_head', 'prod_department_head', 'tenders_manager'],
                    ],
                    [
                        'actions' => ['index', self::URL_UPDATE],
                        'allow' => true,
                        'roles' => ['root', 'assistant', 'accountant', 'accountant_b', 'ecologist', 'ecologist_head', 'prod_department_head', 'tenders_manager'],
                    ],
                    [
                        'actions' => ['delete'],
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
     * Lists all Companies models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompaniesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Companies model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Companies();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if (Yii::$app->user->can('logist')) {
                    Yii::$app->session->setFlash('success', 'Контрагент успешно создан!');
                    return $this->redirect(PoController::ROOT_URL_AS_ARRAY);
                }
                else {
                    return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Companies model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Companies model.
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
        if ($model->checkIfUsed())
            return $this->render('/common/cannot_delete', [
                'details' => [
                    'breadcrumbs' => ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY],
                    'modelRep' => $model->name,
                    'buttonCaption' => self::ROOT_LABEL,
                    'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                ],
            ]);

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the Companies model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Companies the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Companies::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Функция выполняет подбор контрагентов по наименованию от значения переданного в параметрах.
     * Для виджетов Select2.
     * casting-by-typeahead
     * @param $q string
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionCasting($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

		if (empty($q)) {
			return ['results' => []];
		}

        $query = Companies::find()->select([
            'id',
            'text' => 'name',
        ])->andFilterWhere([
            'or',
            ['like', 'name', $q],
            ['like', 'name_short', $q],
            ['like', 'name_full', $q],
            ['like', 'inn', $q],
            ['like', 'ogrn', $q],
        ]);

        return ['results' => $query->asArray()->all()];
    }
}
