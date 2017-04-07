<?php

namespace backend\controllers;

use Yii;
use common\models\Appeals;
use common\models\AppealsSearch;
use common\models\FreshOfficeAPI;
use yii\bootstrap\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * AppealsController implements the CRUD actions for Appeals model.
 */
class AppealsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'update', 'delete', 'try-to-identify-counteragent', 'delegate-counteragent'],
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
                    'delegate-counteragent' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Appeals models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AppealsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ, ОБРАЩЕНИЯ СОЗДАЮТСЯ ТОЛЬКО АВТОМАТИЧЕСКИ
     * Creates a new Appeals model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Appeals();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/appeals']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Appeals model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/appeals']);
        } else {
            // заполним ответственного, если идентифицирован контрагент
            if ($model->fo_id_company != null) {
                $model->fo_id_manager = $model->getCounteragentsReliableField();
            }
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Appeals model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/appeals']);
    }

    /**
     * Finds the Appeals model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Appeals the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Appeals::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Пытается идентифицировать контрагента по имеющимся контактным данным.
     * @param $id integer идентификатор обращения
     * @return array|bool
     */
    public function actionTryToIdentifyCounteragent($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->findModel($id);
            $matches = $model->tryToIdentifyCounteragent();
            $params = [
                'model' => $model,
                // если указывать не здесь form, в самой форме, то все рухнет и не будет работать
                'form' => $form = ActiveForm::begin(),
            ];

            $model->fo_id_company = null;
            $model->fo_company_name = null;
            $model->fo_id_manager = null;
            $model->ca_state_id = null;

            if (count($matches) > 0) {
                if (count($matches) == 1) {
                    $result = $matches[0];
                    $model->fo_id_company = $result['caId'];
                    $model->fo_company_name = $result['caName'];
                    $model->fo_id_manager = $result['managerId'];
                    $model->ca_state_id = $result['stateId'];
                }
                else
                    // другие совпадения, если результатов несколько
                    $params['matches'] = $matches;
            }

            return $this->renderAjax('_ca', $params);
        }
        return false;
    }

    /**
     * Передает контрагента другому менеджеру, отправляет новому менеджеру сообщение об этом,
     * а также создает ему задачу с текстом обращения от заказчика.
     */
    public function actionDelegateCounteragent()
    {
        if (Yii::$app->request->isPost) {
            $ca_id = Yii::$app->request->post('ca_id');
            $appeal_id = Yii::$app->request->post('appeal_id');
            $receiver_id = Yii::$app->request->post('receiver_id');

            $appeal = Appeals::findOne($appeal_id);
            if ($appeal === null) return false;

            $query_text = '
SELECT COMPANY.COMPANY_NAME, COMPANY.ID_MANAGER
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
WHERE COMPANY.ID_COMPANY = ' . $ca_id;
            $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
            if (count($result) > 0) {
                // СОЗДАНИЕ СООБЩЕНИЯ ПОЛЬЗОВАТЕЛЮ
                $sender_id = $result[0]['ID_MANAGER'];
                $message = 'Вам передана компания: ' . $result[0]['COMPANY_NAME'] . '.';
                $params = [
                    'user_id' => $receiver_id,
                    'sender_id' => $sender_id,
                    'text' => $message,
                    // если не заполнять, то заполняется автоматически
                    //'created' => '2017-04-07T16:25:00', // date('Y-m-d\TH:i:s.u', time())
                    'type_id' => FreshOfficeAPI::MESSAGES_TYPE_СООБЩЕНИЕ,
                    'status_id' => FreshOfficeAPI::MESSAGES_STATUS_НЕПРОЧИТАНО,
                ];

                // дата не проставляется автоматически - глюк API Fresh office
                //FreshOfficeAPI::makePostRequestToApi('messages', $params);
                // создание напрямую в базу (требуются права на запись в базу):
                //Appeals::createNewMessageForManager($sender_id, $receiver_id, $message);
                unset($params);

                // СОЗДАНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЮ
                $params = [
                    'company_id' => $ca_id,
                    'user_id' => $receiver_id,
                    //'user_id' => 38, // temporary1
                    'category_id' => FreshOfficeAPI::TASK_CATEGORY_СТАНДАРТНАЯ,
                    'status_id' => FreshOfficeAPI::TASKS_STATUS_ЗАПЛАНИРОВАН,
                    'type_id' => FreshOfficeAPI::TASK_TYPE_НАПОМИНАНИЕ,
                    'date_from' => date('Y-m-d\TH:i:s.u', time()),
                    'date_till' => date('Y-m-d\TH:i:s.u', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))),
                    'note' => $appeal->form_message,
                ];

                //FreshOfficeAPI::makePostRequestToApi('tasks', $params);

                // ПЕРЕДАЧА КОНТРАГЕНТА ДРУГОМУ МЕНЕДЖЕРУ
                Appeals::delegateCounteragent($ca_id, $receiver_id);
            }
        }
    }
}
