<?php

namespace backend\controllers;

use common\models\ProjectsFO;
use Yii;
use common\models\Appeals;
use common\models\AppealsSearch;
use common\models\DirectMSSQLQueries;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

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
                        'actions' => ['index', 'update', 'direct-sql-counteragents-list'],
                        'allow' => true,
                        'roles' => ['root', 'logist'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
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
        $dataProvider = DirectMSSQLQueries::fetchProjects();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
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

        $model = new ProjectsFO();
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
