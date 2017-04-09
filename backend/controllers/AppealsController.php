<?php

namespace backend\controllers;

use common\models\FreshOfficeAPI;
use Yii;
use common\models\Appeals;
use common\models\AppealsSearch;
use common\models\ResponsibleRefusal;
use yii\bootstrap\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
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
                // если указывать form не здесь, а в самой форме, то все рухнет и не будет работать
                'form' => $form = ActiveForm::begin(),
            ];

            $model->fo_id_company = null;
            $model->fo_company_name = null;
            $model->fo_id_manager = null;
            $model->ca_state_id = null;

            if (count($matches) > 0) {
                if (count($matches) == 1)
                    // контрагент идентифицирован однозначно
                    $model->fillUpIdentifiedCounteragentsFields($matches[0]);
                else {
                    // другие совпадения, если результатов несколько
                    // проставим уточненные статусы клиентов
                    // выборка ответственных-отказников
                    $responsibleRefusal = ResponsibleRefusal::find()->select('responsible_id')->asArray()->column();
                    if (count($responsibleRefusal) > 0)
                        foreach ($matches as $index => $match)
                            if (in_array($match['managerId'], $responsibleRefusal)) {
                                // если ответственный в списке отказников - значит клиент просто повторно обращается
                                $matches[$index]['stateId'] = Appeals::CA_STATE_REPEATED;
                                $matches[$index]['stateName'] = $model->getCaStateName($match['stateId']);
                            }
                            else {
                                // если ответственного нет в списке отказников, значит клиент дублирует заявку
                                $matches[$index]['stateId'] = Appeals::CA_STATE_DUPLICATE;
                                $matches[$index]['stateName'] = $model->getCaStateName($match['stateId']);
                            }

                    $params['matches'] = $matches;
                }
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

            if ($ca_id == null) {
                // необходимо создать новую карточку контрагента и назначить его выбранному ответственному
                $result = Appeals::createCounteragent($appeal, $receiver_id);
                if ($result === true)
                    return $this->redirect(['/appeals']);
                else {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return $result;
                }
            }
            else {
                Yii::$app->response->format = Response::FORMAT_JSON;

                // просто передаем контрагента новому ответственному
                $query_text = '
SELECT COMPANY.COMPANY_NAME, COMPANY.ID_MANAGER
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
WHERE COMPANY.ID_COMPANY = ' . $ca_id;
                $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
                if (count($result) > 0) {
                    $sender_id = $result[0]['ID_MANAGER'];
                    return Appeals::delegateCounteragent(
                    // обращение
                        $appeal,
                        // контрагент
                        $ca_id,
                        // новый менеджер
                        $sender_id,
                        // старый менеджер
                        $receiver_id,
                        // текст сообщения
                        str_replace('%COMPANY_NAME%', $result[0]['COMPANY_NAME'], Appeals::TEMPLATE_MESSAGE_BODY_DELEGATING_COUNTERAGENT)
                    );
                }
            }
        }
    }
}
