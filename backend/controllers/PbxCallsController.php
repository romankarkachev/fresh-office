<?php

namespace backend\controllers;

use Yii;
use common\models\pbxCalls;
use common\models\pbxCallsSearch;
use common\models\pbxInternalPhoneNumber;
use common\models\pbxInternalPhoneNumberSearch;
use common\models\pbxCallsComments;
use common\models\pbxCallsCommentsSearch;
use backend\models\pbxIdentifyCounteragentForm;
use common\models\YandexSpeechKitRecognitionQueue;
use common\models\YandexSpeechKitRecognitionQueueSearch;
use common\models\foListPhones;
use common\models\foCompany;
use common\models\YandexServices;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PbxCallsController implements the CRUD actions for pbxCalls model.
 */
class PbxCallsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'pbx-calls';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Звонки';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Звонки';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для постановки в очередь на распознание выделенных пользователем звуковых дорожек
     */
    const URL_TRANSCRIBE_SELECTED_FILES = 'transcribe-selected-files';
    const URL_TRANSCRIBE_SELECTED_FILES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_TRANSCRIBE_SELECTED_FILES];

    /**
     * URL для вывода списка заданий на распознавание, находящихся в очереди
     */
    const URL_RECOGNITION_QUEUE = 'recognition-queue';
    const URL_RECOGNITION_QUEUE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RECOGNITION_QUEUE];

    /**
     * URL для скачивания результатов распознавания
     */
    const URL_DOWNLOAD_RECOGNITION_RESULT = 'download-recognition-result';
    const URL_DOWNLOAD_RECOGNITION_RESULT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_RECOGNITION_RESULT];

    /**
     * URL для удаления из очереди распознавания
     */
    const URL_DELETE_FROM_RECOGNITION_QUEUE = 'delete-from-recognition-queue';
    const URL_DELETE_FROM_RECOGNITION_QUEUE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_FROM_RECOGNITION_QUEUE];

    /**
     * URL для очищения очереди распознавания
     */
    const URL_CLEAR_RECOGNITION_QUEUE = 'clear-recognition-queue';
    const URL_CLEAR_RECOGNITION_QUEUE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CLEAR_RECOGNITION_QUEUE];

    /**
     * Наименование параметра, сохраняемого в сессии при привязке внутреннего номера телефона
     */
    const SESSION_PARAM_NAME_CREATE_INTERNAL_NUMBER = 'pbx_calls_phone_number_';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['identify-counteragent-by-phone'],
                        'allow' => true,
                    ],
                    [
                        'actions' => [
                            'index',
                            'create-internal-number', 'toggle-new', 'preview-file', 'show-comments', 'get-counteragents-name',
                            'identify-counteragent-form', 'validate-identification', 'apply-identification',
                            'render-comments-list', 'validate-comment', 'temp',
                            self::URL_TRANSCRIBE_SELECTED_FILES, self::URL_RECOGNITION_QUEUE, self::URL_DOWNLOAD_RECOGNITION_RESULT,
                            self::URL_DELETE_FROM_RECOGNITION_QUEUE, self::URL_CLEAR_RECOGNITION_QUEUE,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'pbx', 'sales_department_head', 'operator_head'],
                    ],
                    [
                        'actions' => [
                            'create', 'update', 'delete',
                        ],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'toggle-new' => ['POST'],
                    'validate-comment' => ['POST'],
                    'place-new-comment' => ['POST'],
                    'delete' => ['POST'],
                    self::URL_DELETE_FROM_RECOGNITION_QUEUE => ['POST'],
                    self::URL_CLEAR_RECOGNITION_QUEUE => ['POST'],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->can('operator_head') && Yii::$app->user->id != 38) {
            // из старших операторов доступ есть только у г-жи Лукониной
            throw new ForbiddenHttpException('Доступ запрещен.');
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all pbxCalls models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new pbxCallsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'predefinedPeriods' => pbxCallsSearch::fetchPredefinedPeriods(),
            'callsDirections' => pbxCallsSearch::fetchFilterCallDirection(),
            'isNewVariations' => pbxCallsSearch::fetchFilterIsNew(),
        ]);
    }

    /**
     * Creates a new pbxCalls model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new pbxCalls();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(['/pbx-calls', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing pbxCalls model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/pbx-calls']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing pbxCalls model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/pbx-calls']);
    }

    /**
     * Finds the pbxCalls model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return pbxCalls the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = pbxCalls::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Открывает на редактирование внутренний номер телефона или отбор по этому номеру, если владельцев окажется несколько.
     * @param $id integer идентификатор звонка
     * @param $field string src или dst (поле, содержащее внутренний номер компании)
     * @return mixed
     */
    public function actionCreateInternalNumber($id, $field)
    {
        $model = $this->findModel($id);

        $phone_number = $model->{$field};
        $internalNumber = pbxInternalPhoneNumber::find()->where(['phone_number' => $phone_number])->all();
        if (count($internalNumber) > 0) {
            if (count($internalNumber) > 1) {
                return $this->redirect(['/pbx-internal-phone-numbers', (new pbxInternalPhoneNumberSearch())->formName() => ['phone_number' => $phone_number]]);
            }
            else {
                return $this->redirect(['/pbx-internal-phone-numbers/update', 'id' => $internalNumber[0]->id]);
            }
        }
        else {
            Yii::$app->session->set(self::SESSION_PARAM_NAME_CREATE_INTERNAL_NUMBER . Yii::$app->user->id, $phone_number);
            return $this->redirect(['/pbx-internal-phone-numbers/create']);
        }
    }

    /**
     * Переключает отметку "Новый контрагент" в списке звонков.
     * @return bool
     */
    public function actionToggleNew()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = intval(Yii::$app->request->post('id'));
        if ($id > 0) {
            $model = pbxCalls::findOne($id);
            if ($model) {
                $model->is_new = !$model->is_new;
                if ($model->save(false)) return $model->is_new;
            }
        }

        return false;
    }

    /**
     * Рендерит форму комментария к записи разговора.
     * @param $call_id integer идентификатор записи
     * @return string
     */
    private function renderCallCommentForm($call_id)
    {
        return $this->renderAjax('_form_comment', ['model' => new pbxCallsComments(['call_id' => $call_id])]);
    }

    /**
     * Рендерит список комментариев к записи разговора.
     * @param $call_id integer идентификатор записи
     * @return string
     */
    private function renderCallCommentsList($call_id)
    {
        $searchModel = new pbxCallsCommentsSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'call_id' => intval($call_id),
            ],
        ]);

        return $this->renderAjax('_list_comments', [
            'dataProvider' => $dataProvider,
            'model' => new pbxCallsComments(['call_id' => $call_id]),
        ]);
    }

    /**
     * Рендерит список комментариев к записи разговора, идентификатор которого передается в параметрах.
     * В принципе, только для pjax и используется.
     * @param $id integer идентификатор записи
     * @return mixed
     */
    public function actionRenderCommentsList($id)
    {
        return $this->renderCallCommentsList($id);
    }

    /**
     * AJAX-валидация формы добавления нового комментария.
     */
    public function actionValidateComment()
    {
        $model = new pbxCallsComments();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return json_encode(\yii\widgets\ActiveForm::validate($model));
        }
    }

    /**
     * @param $call_id integer идентификатор записи разговора
     * @return mixed
     */
    public function actionShowComments($call_id)
    {
        if (Yii::$app->request->isPost) {
            $model = new pbxCallsComments();

            if (Yii::$app->request->isPjax && $model->load(Yii::$app->request->post())) {
                // безумная защита от задваивания записей
                $time = Yii::$app->formatter->asDate(time(), 'php:Y-m-d H:i:s');
                $user_id = Yii::$app->user->id;
                $exist = pbxCallsComments::findOne(['added_timestamp' => $time, 'user_id' => $user_id, 'contents' => $model->contents]);
                if ($exist == null) {
                    $model->added_timestamp = $time;
                    $model->user_id = $user_id;
                    $model->save();
                }
                return $this->renderCallCommentForm($model->call_id);
            }
        }

        return $this->renderCallCommentForm($call_id) . $this->renderCallCommentsList($call_id);
    }

    /**
     * Показывает окно воспроизведения записи разговора.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPreviewFile($id)
    {
        if (is_numeric($id)) if ($id > 0) {
            $model = pbxCalls::findOne($id);
            $recordsPath = '/mnt/voipnfs/' . Yii::$app->formatter->asDate($model->calldate, 'php:Y.m.d');
            // из-за неправильной настройки времени приходится вычитать час из времени
            $ffp = '';
            foreach (glob("$recordsPath/*$model->uniqueid*") as $filename) {
                if (!empty($filename)) {
                    $ffp = $filename;
                    break;
                }
            }

            if (!empty($ffp) && file_exists($ffp))
                return Yii::$app->response->sendFile($ffp, 'Запись абонент ' . $model->src . '.wav');
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Функция выполняет идентификацию контрагента. 0 - контрагент неоднозначный, -1 - не идентифицирован, [N] - id.
     * Вызывается только Мини-АТС.
     * @param $phone
     * @return integer
     */
    public function actionIdentifyCounteragentByPhone($phone)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // если в начале номера телефона стоит семерка или восьмерка, то убираем их
        $phone = str_replace('+7', '', $phone);
        if ($phone[0] == '7' || $phone[0] == '8') {
            $phone = substr($phone, 1);
        }

        $set = foListPhones::find()
            ->select(foListPhones::tableName() . '.ID_COMPANY')
            ->leftJoin(foCompany::tableName(), foCompany::tableName() . '.ID_COMPANY = ' . foListPhones::tableName() . '.ID_COMPANY')
            ->where(['like', 'TELEPHONE', $phone])
            ->andWhere([
                'or',
                [foCompany::tableName() . '.TRASH' => null],
                [foCompany::tableName() . '.TRASH' => 0],
            ])
            ->groupBy(foListPhones::tableName() . '.ID_COMPANY')
            ->asArray()->all();
        if (count($set) > 0) {
            if (count($set) == 1) {
                return $set[0]['ID_COMPANY'];
            }
            else return pbxCalls::ПРИЗНАК_КОНТРАГЕНТ_ИДЕНТИФИЦИРОВАН_НЕОДНОЗНАЧНО;
        }

        return pbxCalls::ПРИЗНАК_КОНТРАГЕНТ_ВООБЩЕ_НЕ_ИДЕНТИФИЦИРОВАН;
    }

    /**
     * Возвращает наименование контрагента по его идентификатору для заполнения в списке звонков.
     * @param $id
     * @return bool|string
     */
    public function actionGetCounteragentsName($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = foCompany::findOne($id);
        if ($model) {
            return $model->COMPANY_NAME;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function actionIdentifyCounteragentForm($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = pbxCalls::findOne(intval($id));
            if ($model) {
                $formModel = new pbxIdentifyCounteragentForm([
                    'call_id' => $id,
                    'phone' => $model->src,
                    'is_process_other_calls' => true,
                ]);
                if ($model->fo_ca_id === 0) {
                    $formModel->ambiguous = implode(', ', foListPhones::find()
                        ->select('COMPANY_NAME')
                        ->leftJoin(foCompany::tableName(), foCompany::tableName() . '.ID_COMPANY = ' . foListPhones::tableName() . '.ID_COMPANY')
                        ->where(['like', 'TELEPHONE', $model->src])
                        ->andWhere([
                            'or',
                            [foCompany::tableName() . '.TRASH' => null],
                            [foCompany::tableName() . '.TRASH' => 0],
                        ])
                        ->groupBy('COMPANY_NAME,' . foListPhones::tableName() . '.ID_COMPANY')
                        ->orderBy('COMPANY_NAME')
                        ->asArray()->column());
                }

                return $this->renderAjax('_identify_counteragent_form', [
                    'model' => $formModel,
                ]);
            }
        }

        return false;
    }

    /**
     * AJAX-валидация формы идентификации контрагента.
     */
    public function actionValidateIdentification()
    {
        $model = new pbxIdentifyCounteragentForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return Json::encode(\yii\widgets\ActiveForm::validate($model));
        }
    }

    /**
     * Отправляет приглашение перевозчику создать аккаунт в личном кабинете.
     * @return array|bool
     */
    public function actionApplyIdentification()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new pbxIdentifyCounteragentForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->applyIdentification()) {
                return [
                    'result' => true,
                    'call_id' => $model->call_id,
                    'replaceAll' => $model->is_process_other_calls,
                    'phone' => $model->call->src,
                    'ca_id' => $model->fo_ca_id,
                    'ca_name' => $model->fo_ca_name,
                ];
            }
        }

        return false;
    }

    /**
     * Отправляет файлы выделенных пользователем записей разговоров на сервер Яндекс (помещает их в бакет). Ставит в
     * очередь на распознание каждый отправленный файл, сохраняя идентификатор ответа для того, чтобы потом проверять
     * готовность распознания.
     * transcribe-selected-files
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTranscribeSelectedFiles()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $errors = [];
        $ids = [];
        if (Yii::$app->request->post('ids') !== null) {
            $calls = PbxCalls::find()->where(['id' => Yii::$app->request->post('ids')])->all();
            if (count($calls) > 0) {
                $client = new \yii\httpclient\Client([
                    'baseUrl' => YandexServices::URL_SPEECHKIT_RECOGNITION_RUN_LONG,
                    'transport' => 'yii\httpclient\CurlTransport'
                ]);
                foreach ($calls as $call) {
                    $ffp = '';
                    $recordsPath = '/mnt/voipnfs/' . Yii::$app->formatter->asDate($call->calldate, 'php:Y.m.d');
                    foreach (glob("$recordsPath/*$call->uniqueid*") as $filename) {
                        if (!empty($filename)) {
                            $ffp = $filename;
                            break;
                        }
                    }

                    if (!empty($ffp) && file_exists($ffp)) {
                        /** @var $result \chemezov\yii2\yandex\cloud\Service */
                        /** @var $result \Aws\ResultInterface */
                        $result = Yii::$app->yandexCloud->upload($call->id . '.wav', $ffp);
                        if (isset($result->toArray()['ObjectURL'])) {
                            // файл успешно загружен в бакет Yandex Cloud
                            $objectUrl = $result->toArray()['ObjectURL'];

                            // отправим его на распознавание
                            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(\yii\httpclient\Client::FORMAT_JSON)
                                ->setData([
                                    'config' => [
                                        'specification' => [
                                            'languageCode' => 'ru-RU',
                                            'audioEncoding' => 'LINEAR16_PCM',
                                            'sampleRateHertz' => 8000,
                                            'audioChannelCount' => 1,
                                        ],
                                    ],
                                    'audio' => [
                                        'uri' => $objectUrl,
                                    ],
                                ])
                                ->setHeaders([
                                    'Authorization' => 'Api-Key ' . YandexServices::SPEECHKIT_API_KEY,
                                ])->send();

                            if ($response->isOk) {
                                // помещаем файл в очередь на распознавание
                                if (!(new YandexSpeechKitRecognitionQueue([
                                    'check_after' => (time() + max(10, 10 * round($call->duration / 60, 0, PHP_ROUND_HALF_UP))),
                                    'call_id' => $call->id,
                                    'url_bucket' => $objectUrl,
                                    'operation_id' => $response->data['id'],
                                ]))->save()) {
                                    $ids[] = $call->id;
                                    $errors[] = 'Не удалось поставить в очередь (' . $call->id . ')!';
                                    Yii::$app->yandexCloud->delete($call->id . '.wav');
                                }
                            }
                            else {
                                // что-то не задалось, удаляем файл из хранилища
                                $result = Yii::$app->yandexCloud->delete($call->id . '.wav');
                                // отмечаем проблемную аудиодорожку
                                $ids[] = $call->id;
                                $errors[] = 'Не удалось отправить на распознавание (' . $call->id . ')!';
                            }
                        }
                        else {
                            $ids[] = $call->id;
                            $errors[] = 'Не удалось отправить в бакет (' . $call->id . ')!';
                        }
                    }
                    else {
                        $ids[] = $call->id;
                        $errors[] = 'Файл аудиодорожки звонка ' . $call->id . ' не найден!';
                    }
                }
            }
            else $errors[] = 'Звонки не идентифицированы!';
        }
        else $errors[] = 'Неверно переданы параметры';

        if (!empty($errors)) {
            return [
                'result' => false,
                'errors' => $errors,
                'ids' => $ids,
            ];
        }
        else return true;
    }

    /**
     * Отдает на скачивание файл с результатами распознавания.
     * download-recognition-result
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDownloadRecognitionResult($id)
    {
        if (is_numeric($id)) if ($id > 0) {
            $model = $this->findModel($id);

            if (!empty($model->recognitionFfp) && file_exists($model->recognitionFfp))
                return Yii::$app->response->sendFile($model->recognitionFfp, 'Запись абонент ' . $model->src . '.txt');
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Выводит список заданий, находящихся на распознавании в Яндексе.
     * recognition-queue
     * @return mixed
     */
    public function actionRecognitionQueue()
    {
        $searchModel = new YandexSpeechKitRecognitionQueueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('/recognition-queue/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Выполняет удаление из очереди.
     * delete-from-recognition-queue
     * @param $id
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFromRecognitionQueue($id)
    {
        (YandexSpeechKitRecognitionQueue::findOne($id))->delete();

        return $this->redirect(PbxCallsController::URL_RECOGNITION_QUEUE_AS_ARRAY);
    }

    /**
     * Выполняет удаление всех записей.
     * clear-recognition-queue
     * @return mixed
     */
    public function actionClearRecognitionQueue()
    {
        if (false !== YandexSpeechKitRecognitionQueue::deleteAll()) {
            Yii::$app->getSession()->setFlash('success', 'Все записи были удалены.');
        }

        return $this->redirect(PbxCallsController::URL_RECOGNITION_QUEUE_AS_ARRAY);
    }

    public function actionTemp()
    {
        /*
        // делает запрос, не выполнено ли распознавание
        $client = new \yii\httpclient\Client([
            'baseUrl' => YandexServices::URL_SPEECHKIT_RECOGNITION_RESULTS,
            'transport' => 'yii\httpclient\CurlTransport'
        ]);
        $response = $client->createRequest()
            ->setUrl('e03v4m6v1meg04qpkqsi')
            ->setMethod('GET')
            ->setHeaders([
                'Authorization' => 'Api-Key AQVNyyn6FCwYKpNIYEkg6UFq-wYn_L5ocLKzp7F6',
            ])->send();

        if ($response->isOk) {
            var_dump($response->data);
        }
        else {
            var_dump($response->statusCode, $response->content);
        }
        return;
        */

        /*
        //$model = pbxCalls::findOne(675388);
        //if ($model) {
            $client = new \yii\httpclient\Client([
                'baseUrl' => YandexServices::URL_SPEECHKIT_RECOGNITION_RUN_LONG,
                'transport' => 'yii\httpclient\CurlTransport'
            ]);
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setFormat(\yii\httpclient\Client::FORMAT_JSON)
                ->setData([
                    'config' => [
                        'specification' => [
                            'languageCode' => 'ru-RU',
                            'audioEncoding' => 'LINEAR16_PCM',
                            'sampleRateHertz' => 8000,
                            'audioChannelCount' => 1,
                        ],
                    ],
                    'audio' => [
                        'uri' => 'https://storage.yandexcloud.net/1nok/263.wav',
                    ],
                ])
                ->setHeaders([
                    'Authorization' => 'Api-Key AQVNyyn6FCwYKpNIYEkg6UFq-wYn_L5ocLKzp7F6',
                ])->send();

            if ($response->isOk) {
                var_dump($response->data);
            }
            else {
                var_dump($response->statusCode, $response->content);
            }
        //}
        */
    }
}
