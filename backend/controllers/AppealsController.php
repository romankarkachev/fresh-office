<?php

namespace backend\controllers;

use Yii;
use common\models\Appeals;
use common\models\AppealsSearch;
use common\models\DirectMSSQLQueries;
use yii\bootstrap\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

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
                'rules' => [
                    [
                        'actions' => ['index', 'update', 'delete', 'try-to-identify-counteragent', 'after-identifying-ambiguous', 'delegate-counteragent'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        // Создание обращений вручную только для Полных прав и Оператора
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['root', 'operator'],
                    ],
                    [
                        // Мастер обработки обращений только для Полных прав и Работа с отчетом по оборотам клиентов
                        'actions' => ['wizard'],
                        'allow' => true,
                        'roles' => ['root', 'role_report1'],
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
     * Создает новое обращение.
     * Если создание выполнено успешно, браузер редиректит в список обращений.
     * Либо на страницу создания нового обращения, если создание выполнял оператор.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Appeals();
        $model->scenario = 'create_manual';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->files = UploadedFile::getInstances($model, 'files');
            if (count($model->files) > 0) {
                if (!$model->upload()) {
                    Yii::$app->session->setFlash('error', 'Не удалось загрузить файлы.');
                    return $this->render('creating_form_operator', [
                        'model' => $model,
                    ]);
                }

                // отправляем загруженные файлы
                $model->sendEmailIfFilesAre();
            }

            Yii::$app->session->setFlash('success', 'Обращение успешно создано.');

            if (Yii::$app->user->can('root'))
                return $this->redirect(['/appeals']);
            else
                return $this->redirect(['/appeals/create']);
        }

        return $this->render('creating_form_operator', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Appeals model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
//        $time = time();
//        $formatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::NONE, \IntlDateFormatter::LONG, 'Europe/Moscow');
//        echo $formatter->format($time), PHP_EOL;
//        echo "ICU: " . INTL_ICU_VERSION . "\n";
//        return;
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
     * Мастер обработки обращений.
     * Вызывает редактирование обращений в определенных статусах до тех пор, пока новая выборка не окажется пустой.
     * @return string|Response
     */
    public function actionWizard()
    {
        $model = Appeals::find()
            ->where(['in', 'state_id', [
                // новые
                Appeals::APPEAL_STATE_NEW,
                // выбор ответственного
                Appeals::APPEAL_STATE_RESPONSIBLE
            ]])
            ->orWhere(['in', 'ca_state_id', [
                // неоднозначные
                Appeals::CA_STATE_AMBIGUOUS
            ]])
            ->orderBy('created_at')
            ->one();
        if ($model == null) return $this->render('wizard_empty_dataset');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // но пока сюда никто никакой post-запрос не отправляет
            return $this->redirect(['/appeals/wizard']);
        } else {
            // заполним ответственного, если идентифицирован контрагент
            if ($model->fo_id_company != null) {
                $model->fo_id_manager = $model->getCounteragentsReliableField();
            }
            return $this->render('update', [
                'model' => $model,
                'is_wizard' => true,
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
     * @return mixed|bool
     */
    public function actionTryToIdentifyCounteragent($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->findModel($id);
            // пытаемся идентифицировать контрагента
            $matches = $model->tryToIdentifyCounteragent();
            $params = [
                'model' => $model,
                // если указывать form не здесь, а в самой форме, то все рухнет и не будет работать
                'form' => $form = ActiveForm::begin(),
            ];
            // заполняем статусы клиента и обращения
            $model->fillStates($matches);

            if (count($matches) > 0)
                if (count($matches) == 1)
                    // если контрагент идентифицирован однозначно, сохраним сразу модель
                    $model->save();
                else
                    $params['matches'] = $matches;

            return $this->renderAjax('_ca', $params);
        }

        return false;
    }

    /**
     * Вызывается после выбора контрагента из списка подходящих при неоднозначной его идентификации.
     * @param $appeal_id integer идентификатор обращения
     * @param $ca_id integer идентификатор контрагента
     * @return mixed|bool
     */
    public function actionAfterIdentifyingAmbiguous($appeal_id, $ca_id)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->findModel($appeal_id);
            $matches = DirectMSSQLQueries::fetchCounteragent($ca_id);
            $params = [
                'model' => $model,
                // если указывать form не здесь, а в самой форме, то все рухнет и не будет работать
                'form' => $form = ActiveForm::begin(),
            ];
            // заполняем статусы клиента и обращения
            $model->fillStates($matches);
            $model->save();
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
            $ca_id = intval(Yii::$app->request->post('ca_id'));
            $appeal_id = intval(Yii::$app->request->post('appeal_id'));
            $receiver_id = intval(Yii::$app->request->post('receiver_id'));

            $appeal = Appeals::findOne($appeal_id);
            if ($appeal === null) return false;

            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($ca_id == null) {
                // необходимо создать новую карточку контрагента и назначить его выбранному ответственному
                $result = Appeals::createCounteragent($appeal, $receiver_id);
                return $result;
            }
            else {
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
