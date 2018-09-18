<?php

namespace backend\controllers;

use Yii;
use common\models\pbxCalls;
use common\models\pbxCallsSearch;
use common\models\pbxInternalPhoneNumber;
use common\models\pbxInternalPhoneNumberSearch;
use common\models\pbxCallsCommentsSearch;
use common\models\foListPhones;
use common\models\foCompany;
use common\models\pbxCallsComments;
use backend\models\pbxIdentifyCounteragentForm;
use yii\web\Controller;
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
                'class' => AccessControl::className(),
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
                            'render-comments-list', 'validate-comment',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'pbx', 'sales_department_head'],
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'toggle-new' => ['POST'],
                    'validate-comment' => ['POST'],
                    'place-new-comment' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
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
            Yii::$app->response->format = Response::FORMAT_JSON;
            echo json_encode(\yii\widgets\ActiveForm::validate($model));
            Yii::$app->end();
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
     * @throws NotFoundHttpException если файл не будет обнаружен
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
            Yii::$app->response->format = Response::FORMAT_JSON;
            echo json_encode(\yii\widgets\ActiveForm::validate($model));
            Yii::$app->end();
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
}
