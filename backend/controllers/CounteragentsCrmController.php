<?php

namespace backend\controllers;

use Yii;
use common\models\foCompany;
use common\models\foCompanySearch;
use common\models\Edf;
use common\models\DocumentsTypes;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * CounteragentsCrmController implements the CRUD actions for foCompany model.
 */
class CounteragentsCrmController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT = 'counteragents-crm';
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = self::URL_ROOT;

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Контрагенты CRM';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY];

    /**
     * URL для добавления записи
     */
    const URL_CREATE = 'create';
    const URL_CREATE_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_CREATE];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';
    const URL_UPDATE_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_UPDATE];

    /**
     * URL для подготовки к созданию электронного документа
     */
    const URL_COMPOSE_EDF = 'compose-edf';
    const URL_COMPOSE_EDF_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_COMPOSE_EDF];

    /**
     * URL для подготовки к созданию электронного документа
     */
    const URL_COMPOSE_TASK = 'compose-task';
    const URL_COMPOSE_TASK_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_COMPOSE_TASK];

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
                        'actions' => [self::URL_CREATE],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        'actions' => ['index', self::URL_UPDATE, self::URL_COMPOSE_EDF, self::URL_COMPOSE_TASK],
                        'allow' => true,
                        'roles' => [
                            'root', 'assistant', 'accountant', 'accountant_b', 'ecologist', 'ecologist_head',
                            'prod_department_head', 'tenders_manager', 'sales_department_manager',
                        ],
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
     * Lists all foCompany models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new foCompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new foCompany model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new foCompany();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::URL_ROOT_AS_ARRAY);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing foCompany model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $edfInfo = '';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(ArrayHelper::merge(self::URL_UPDATE_AS_ARRAY, ['id' => $model->ID_COMPANY]));
        }
        else {
            /*
            // делаем выборку договоров из документооборота
            $contracts = [];
            foreach (Edf::find()->where(['fo_ca_id' => intval($id), 'type_id' => DocumentsTypes::TYPE_ДОГОВОР])->orderBy('doc_date_expires')->all() as $contract) {
                if (strtotime(Yii::$app->formatter->asDate(time(), 'php:d.m.Y') . ' 00:00:00') <= strtotime($contract->doc_date_expires . ' 00:00:00')) {
                    $contracts[] = '№ ' . $contract->doc_num . ' от ' . Yii::$app->formatter->asDate($contract->doc_date, 'php:d.m.Y г.') . ' (<abbr title="' . $contract->contractTypeName . ' с ' . $contract->organizationName . '"><span class="text-success">до ' . Yii::$app->formatter->asDate($contract->doc_date_expires, 'php:d.m.Y г.') . '</span></abbr>)';
                }
            }

            $edfInfo = implode(', ', $contracts);
            $edfInfo = trim($edfInfo, ', ');
            unset($contracts);
            */
        }

        return $this->render('update', [
            'model' => $model,
            'edfInfo' => $edfInfo,
        ]);
    }

    /**
     * Deletes an existing foCompany model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        //$this->findModel($id)->delete();
        Yii::$app->session->setFlash('info', 'Функция удаления временно недоступна.');

        return $this->redirect(self::URL_ROOT_AS_ARRAY);
    }

    /**
     * Finds the foCompany model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return foCompany the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = foCompany::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Выполняет подготовку электронного документа. Переводит на страницу создания с заполненными данными контрагента.
     * @param $id integer идентификатор контрагента
     * @return \yii\web\Response
     */
    public function actionComposeEdf($id)
    {
        Yii::$app->session->set('fo_ca_id_for_edf_' . Yii::$app->user->id, $id);
        return $this->redirect(EdfController::URL_CREATE_AS_ARRAY);
    }

    /**
     * Выполняет подготовку задачи. Переводит на страницу создания с заполненными данными контрагента.
     * @param $id integer идентификатор контрагента
     * @return \yii\web\Response
     */
    public function actionComposeTask($id)
    {
        Yii::$app->session->set('fo_ca_id_for_task_' . Yii::$app->user->id, $id);
        return $this->redirect(TasksController::URL_CREATE_AS_ARRAY);
    }
}
