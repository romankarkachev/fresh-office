<?php

namespace backend\controllers;

use common\models\PaymentOrders;
use Yii;
use common\models\DirectMSSQLQueries;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\AssignFerrymanForm;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Работа с проектами Fresh Office.
 */
class ProjectsController extends Controller
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
                        'actions' => ['direct-sql-counteragents-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'index', 'update', 'assign-ferryman-form', 'compose-ferryman-fields', 'assign-ferryman',
                            'create-order-by-selection',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'sales_department_manager', 'head_assist'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign-ferryman' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает список проектов.
     * Выборка через API Fresh Office.
     */
    public function actionIndex()
    {
        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Updates an existing HandlingKinds model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        // не работает
        /*
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            //$model->save();
            return $this->redirect(['/projects']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        */

        $query_text = '
SELECT LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AS id
,LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT AS type_id, LIST_SPR_PROJECT.NAME_PROJECT AS type_name
,ADD_vivozdate
,[DATE_START_PROJECT] AS date_start
,[DATE_FINAL_PROJECT] AS date_end
,LIST_PROJECT_COMPANY.ID_COMPANY AS ca_id, COMPANY.COMPANY_NAME AS ca_name
,LIST_PROJECT_COMPANY.ID_MANAGER_VED AS manager_id, MANAGERS.MANAGER_NAME AS manager_name
,payment.amount
,payment.cost
,LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT AS state_id, LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT AS state_name
,ADD_perevoz AS perevoz
,ADD_proizodstvo AS proizodstvo
,ADD_oplata AS oplata
,ADD_adres AS adres
,ADD_dannie AS dannie
,ADD_ttn AS ttn
,ADD_wieght AS weight
FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_PROJECT_COMPANY]
LEFT JOIN LIST_SPR_PROJECT ON LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT
LEFT JOIN LIST_SPR_PRIZNAK_PROJECT ON LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = LIST_PROJECT_COMPANY.ID_MANAGER_VED
LEFT JOIN (
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR) AS amount, SUM(SS_PRICE_TOVAR) AS cost
	FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR_PROJECT]
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS payment ON payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY
WHERE LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY=' . $id;

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();

        $model = new foProjects();
        $model->attributes = $result[0];
        $model->isNewRecord = false;

        if ($model->load(Yii::$app->request->post())) {
            //$model->save();
            return $this->redirect(['/projects']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the AppealSources model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return foProjects the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = foProjects::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Формирует и отдает форму назначения перевозичка.
     * @param $ids string идентификаторы проектов
     * @return mixed
     */
    public function actionCreateOrderBySelection($ids)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->session->set('ids_for_payment_order_' . Yii::$app->user->id, explode(',', $ids));
            return $this->redirect('/payment-orders/create');
        }

        return '';
    }

    /**
     * Формирует и отдает форму назначения перевозичка.
     * @param $ids string идентификаторы проектов
     * @return mixed
     */
    public function actionAssignFerrymanForm($ids)
    {
        if (Yii::$app->request->isAjax) {
            $model = new AssignFerrymanForm();
            $model->project_ids = explode(',', $ids);

            return $this->renderAjax('_assign_ferryman_form', [
                'model' => $model,
            ]);
        }

        return '';
    }

    /**
     * Формирует поля Водитель и Транспорт для выбранного пользователем перевозчика.
     * @param $ferryman_id integer идентификатор сделки
     * @return mixed
     */
    public function actionComposeFerrymanFields($ferryman_id)
    {
        if (Yii::$app->request->isAjax) {
            $model = new AssignFerrymanForm();
            $model->ferryman_id = $ferryman_id;

            return $this->renderAjax('_assign_ferryman_fields', [
                'model' => $model,
                'form' => \yii\bootstrap\ActiveForm::begin(),
            ]);
        }

        return '';
    }

    /**
     * Назначает перевозчика, водителя и транспорт выбранным проектам.
     * @return mixed
     */
    public function actionAssignFerryman()
    {
        Url::remember(Yii::$app->request->referrer);
        if (Yii::$app->request->isPost) {
            $model = new AssignFerrymanForm();
            if ($model->load(Yii::$app->request->post())) {
                $driver = $model->driver->surname . ' ' . $model->driver->name;
                $driver = trim($driver);
                $driver .= ' ' . $model->driver->patronymic;
                $driver = trim($driver);
                if ($driver != '') {
                    // паспортные данные
                    $driver .= ', паспорт ' . $model->driver->pass_serie;
                    $driver = trim($driver);
                    if ($model->driver->pass_num != null && $model->driver->pass_num != '') $driver .= ' № ' . $model->driver->pass_num;
                    $driver = trim($driver);
                    if ($model->driver->pass_issued_at != null) $driver .= ' выдан ' . Yii::$app->formatter->asDate($model->driver->pass_issued_at, 'php:d.m.Y');
                    $driver = trim($driver);
                    $driver .= ' ' . $model->driver->pass_issued_by;
                    $driver = trim($driver);

                    // водительское удостоверение
                    $driver .= ', вод. удост. ' . $model->driver->driver_license;
                    if ($model->driver->dl_issued_at != null) $driver .= ' ' . Yii::$app->formatter->asDate($model->driver->dl_issued_at, 'php:d.m.Y');
                    $driver = trim($driver);
                }

                $data = $model->transport->representation . ' ' . $driver;
                if (DirectMSSQLQueries::assignFerryman($model->project_ids, $model->ferryman->name, $data))
                    Yii::$app->session->setFlash('success', 'Перевозчик успешно назначен в проекты ' . implode(',', $model->project_ids) . '.');
                else
                    Yii::$app->session->setFlash('error', 'Не удалось загрузить файлы.');

                $this->goBack();
            }
        }

        return '';
    }

    /**
     * Функция выполняет поиск контрагента по наименованию, переданному в параметрах.
     * @param $q string
     * @return array
     */
    public function actionDirectSqlCounteragentsList($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['results' => DirectMSSQLQueries::fetchCounteragents($q)];
    }
}
