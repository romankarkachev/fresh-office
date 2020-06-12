<?php

namespace backend\controllers;

use Yii;
use common\models\Po;
use common\models\PoSearch;
use common\models\PoFiles;
use common\models\PoEig;
use common\models\PoValues;
use common\models\PaymentOrdersStates;
use common\models\PoEi;
use common\models\PoPop;
use common\models\PoEp;
use common\models\UsersEiApproved;
use common\models\UsersEiAccess;
use common\models\Companies;
use common\models\ReportPoAnalytics;
use common\models\foProjects;
use common\models\EcoProjects;
use common\models\AuthItem;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use moonland\phpexcel\Excel;

/**
 * PoController implements the CRUD actions for Po model.
 */
class PoController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'po';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Платежные ордеры (бюджет)';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    /**
     * URL ренденрит поле для выбора проекта по экологии
     */
    const URL_RENDER_AF_BLOCK = 'render-additional-field-block';
    const URL_RENDER_AF_BLOCK_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_AF_BLOCK];

    /**
     * URL для импорта информации о начислениях зарплаты в платежные ордеры из Excel
     */
    const URL_IMPORT_SALARY = 'import-salary';
    const URL_IMPORT_SALARY_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_IMPORT_SALARY];

    /**
     * URL для импорта информации о налогах на ФОТ в платежные ордеры из Excel
     */
    const URL_IMPORT_WAGE_FUND = 'import-wage-fund';
    const URL_IMPORT_WAGE_FUND_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_IMPORT_WAGE_FUND];

    /**
     * URL для загрузки файлов через ajax
     */
    const URL_UPLOAD_FILES = 'upload-files';
    const URL_UPLOAD_FILES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPLOAD_FILES];

    /**
     * URL для скачивания приаттаченных файлов
     */
    const URL_DOWNLOAD_FILE = 'download-file';

    /**
     * URL для предварительного просмотра файлов через ajax
     */
    const URL_PREVIEW_FILE = 'preview-file';
    const URL_PREVIEW_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PREVIEW_FILE];

    /**
     * URL для удаления файла через ajax
     */
    const URL_DELETE_FILE = 'delete-file';
    const URL_DELETE_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_FILE];

    /**
     * URL для рендера блока со свойствами статьи расходов
     */
    const URL_RENDER_PROPERTIES = 'render-properties';
    const URL_RENDER_PROPERTIES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_PROPERTIES];

    /**
     * URL для изменения статуса на "Оплачено" на лету
     */
    const URL_SET_PAID_ON_THE_FLY = 'set-paid-on-the-fly';
    const URL_SET_PAID_ON_THE_FLY_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_SET_PAID_ON_THE_FLY];

    /**
     * URL для интерактивного удаления привязки значения свойства к статье расходов платежного ордера
     */
    const URL_DELETE_VALUE_LINK = 'delete-value-link';
    const URL_DELETE_VALUE_LINK_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_VALUE_LINK];

    /**
     * URL для выдачи денег подотчет
     */
    const URL_ADVANCE_REPORT = 'advance-report';
    const URL_ADVANCE_REPORT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_ADVANCE_REPORT];

    /**
     * URL рендерит поле с причиной проигрыша (отказа)
     */
    const URL_RENDER_FIELD_REASON = 'render-field-reason';
    const URL_RENDER_FIELD_REASON_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_FIELD_REASON];

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
                        'actions' => [self::URL_DOWNLOAD_FILE],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update', 'delete', self::URL_RENDER_PROPERTIES, self::URL_RENDER_AF_BLOCK,
                            self::URL_SET_PAID_ON_THE_FLY, self::URL_UPLOAD_FILES, self::URL_PREVIEW_FILE, self::URL_DELETE_FILE,
                            self::URL_DELETE_VALUE_LINK, self::URL_IMPORT_SALARY, self::URL_IMPORT_WAGE_FUND,
                            self::URL_RENDER_FIELD_REASON,
                        ],
                        'allow' => true,
                        'roles' => [
                            'root', 'logist', 'assistant', 'accountant', 'accountant_b', 'ecologist', 'ecologist_head',
                            'prod_department_head', 'tenders_manager', 'sales_department_manager', AuthItem::ROLE_ACCOUNTANT_SALARY,
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    self::URL_DELETE_FILE => ['POST'],
                    self::URL_DELETE_VALUE_LINK => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Po models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $roleName = Yii::$app->user->identity->getRoleName();

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(self::ROOT_URL_AS_ARRAY);
                return false;
            }

            // именно для экспорта постраничный переход отключается, чтобы в файл выгружались все записи
            $dataProvider->pagination = false;
            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Платежные ордеры бюджет (сформирован '.date('Y-m-d в H i').').xlsx',
                'asAttachment' => true,
                'columns' => [
                    [
                        'attribute' => 'paid_at',
                        'header' => 'Дата оплаты',
                        'format' => ['datetime', 'dd.MM.YYYY'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '130'],
                    ],
                    'stateName:ntext:Статус',
                    'companyName',
                    'eiRep',
                    'amount',
                    'comment',
                    [
                        'attribute' => 'created_at',
                        'header' => 'Создан',
                        'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                    ],
                ],
            ]);
        }
        else {
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            $params = [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'eiApproved' => [],
                'eiAmount' => 0,
                'queryString' => $queryString,
                'roleName' => $roleName,
            ];

            if ($roleName == AuthItem::ROLE_ACCOUNTANT_B) {
                // для бухгалтеров по бюджету прикрепляем дополнительную переменную, в которой будут доступные для согласования
                // без ведома руководства статьи расходов
                if (!empty(Yii::$app->user->identity->profile->po_maa)) {
                    $params['eiApproved'] = UsersEiApproved::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])->column();
                    $params['eiAmount'] = (float)Yii::$app->user->identity->profile->po_maa;
                }
            }

            return $this->render('index', $params);
        }
    }

    /**
     * Creates a new Po model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \Throwable
     */
    public function actionCreate()
    {
        $model = new Po();
        $dpProperties = [];

        if ($model->load(Yii::$app->request->post())) {
            // объект автоматически создается в статусе черновика
            $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК;

            $success = true;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // заполнены ли свойства статьи
                    if (!empty($model->propertiesValues)) {
                        // создадим соответствующие записи в базе данных
                        foreach ($model->propertiesValues as $index => $row) {
                            if (!(new PoPop([
                                'po_id' => $model->id,
                                'ei_id' => $model->ei_id,
                                'property_id' => $index,
                                'value_id' => $row['value_id'],
                            ]))->save()) {
                                $success = false;
                                $model->addError('propertiesValues', 'Не все значения свойств статьи заполнены.');
                                break;
                            }
                        }
                        $dpProperties = $model->getPropertiesAsFilledArray();
                    }

                    // выбран ли платежный ордер
                    if (!empty($model->ep) && $success) {
                        (new PoEp([
                            'po_id' => $model->id,
                            'ep_id' => $model->ep,
                        ]))->save() ? null : $success = false;
                    }
                }
                else {
                    $success = false;
                }

                $success ? $transaction->commit() : $transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            if ($success) {
                return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'dpProperties' => $dpProperties,
        ]);
    }

    /**
     * Updates an existing Po model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException при попытке открыть чужой платежный ордер
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $currentUserId = Yii::$app->user->id;
        $model = $this->findModel($id);

        // отдельные касты пользователей могут открывать только свои платежные ордеры
        $roleName = Yii::$app->user->identity->getRoleName();
        if (!ArrayHelper::isIn($roleName, [
            AuthItem::ROLE_ROOT,
            AuthItem::ROLE_ACCOUNTANT_B,
            AuthItem::ROLE_ACCOUNTANT_SALARY,
        ]) && $model->created_by != $currentUserId) {
            throw new ForbiddenHttpException('Доступ запрещен.');
        }
        elseif ($roleName == AuthItem::ROLE_ACCOUNTANT_SALARY && !ArrayHelper::isIn($model->ei_id, UsersEiAccess::find()->select('ei_id')->where(['user_id' => $currentUserId])->column())) {
            // пользователь в роли бухгалтера по зарплате пытается открыть платежный ордер со статьей, которая ему не должна быть доступна
            throw new ForbiddenHttpException('Доступ запрещен.');
        }

        $state = $model->state_id;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                // если нажата кнопка "Отправить на согласование"
                if (Yii::$app->request->post('order_ready') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ;

                // если нажата кнопка "Согласовать"
                elseif (Yii::$app->request->post('order_approve') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН;

                // если нажата кнопка "Отказать"
                elseif (Yii::$app->request->post('order_reject') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ;

                // если нажата кнопка "Подать повторно"
                elseif (Yii::$app->request->post('order_repeat') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК;

                // если нажата кнопка "Оплачено"
                elseif (Yii::$app->request->post('order_paid') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН;

                $success = true;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // выбран ли платежный ордер
                        if (!empty($model->ep)) {
                            $poEp = PoEp::findOne(['po_id' => $model->id]);
                            if ($poEp) {
                                $poEp->updateAttributes(['ep_id' => $model->ep]);
                            }
                            else {
                                (new PoEp([
                                    'po_id' => $model->id,
                                    'ep_id' => $model->ep,
                                ]))->save() ? null : $success = false;
                            }
                        }
                        else {
                            PoEp::deleteAll(['po_id' => $model->id]);
                        }
                    }
                    else {
                        // возвращаем статус
                        $success = false;
                        $model->state_id = $state;
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }

                if ($success) {
                    $transaction->commit();
                    if (Yii::$app->request->post('order_repeat') !== null) {
                        return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $id]);
                    }
                    else {
                        return $this->redirect(self::ROOT_URL_AS_ARRAY);
                    }
                }
                else {
                    // возвращаем статус
                    $model->state_id = $state;
                }
            }
        }
        else {
            $ecoProject = EcoProjects::find()->where(['id' => PoEp::find()->select('ep_id')->where(['po_id' => $id])])->one();
            $model->ep = $ecoProject->id;
        }

        // параметры, которые передаются в форму
        $params = [
            'model' => $model,
            'dpFiles' => $model->getFilesAsDataProvider(),
            'dpLogs' => $model->getLogsAsDataProvider(),
        ];

        if (!empty($ecoProject)) {
            $params['ecoProject'] = $ecoProject;
        }

        if ($roleName == AuthItem::ROLE_ACCOUNTANT_B) {
            // для бухгалтеров по бюджету прикрепляем дополнительную переменную, в которой будут доступные для согласования
            // без ведома руководства статьи расходов
            if (!empty(Yii::$app->user->identity->profile->po_maa)) {
                $params['eiApproved'] = UsersEiApproved::find()->select('ei_id')->where(['user_id' => $currentUserId])->column();
                $params['eiAmount'] = Yii::$app->user->identity->profile->po_maa;
            }
        }

        $formName = 'update';
        switch ($model->state_id) {
            case PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК:
                $params['dpProperties'] = $model->getPropertiesAsFilledArray();
                break;
            default:
                $formName = 'view';
                $params['dpProperties'] = $model->getPropertiesAsDataProvider();
                break;
        }

        return $this->render($formName, $params);
    }

    /**
     * Deletes an existing Po model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can(AuthItem::ROLE_ROOT)) {
            // все пользователи просто помечают на удаление, не могут удалить
            $model->updateAttributes(['is_deleted' => true]);
        }
        else {
            $model->delete();
        }
        $urlToReturnTo = self::ROOT_URL_AS_ARRAY;
        if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК) {
            $urlToReturnTo = \yii\helpers\Url::to(ArrayHelper::merge($urlToReturnTo, [(new PoSearch())->formName() => ['state_id' => $model->state_id]]));
        }

        return $this->redirect($urlToReturnTo);
    }

    /**
     * Finds the Po model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Po the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Po::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Рендерит блок со свойствами, которыми описана переданная в параметрах статья расходов.
     * render-properties
     * @param $ei_id integer статья расходов
     * @return mixed
     */
    public function actionRenderProperties($ei_id)
    {
        if (Yii::$app->request->isAjax) {
            $expenditureItem = PoEi::findOne($ei_id);
            if ($expenditureItem) {
                return $this->renderAjax('_properties_block', ['properties' => Po::fetchExpenditureItemPropertiesValuesAsArray($expenditureItem)]);
            }

            return '';
        }
    }

    /**
     * Рендерит блок с полем "Проект по экологии".
     * @param $ei_id int режим (1 - поле "Проект", 2 - "Проект по экологии")
     * @return mixed
     */
    public function actionRenderAdditionalFieldBlock($ei_id)
    {
        if (Yii::$app->request->isAjax) {
            $ei = PoEi::findOne($ei_id);
            if ($ei) {
                switch ($ei->group_id) {
                    case (PoEig::ГРУППА_ТРАНСПОРТ && $ei_id == PoEi::СТАТЬЯ_ПЕРЕВОЗЧИКИ):
                        $viewName = '_field_project';
                        break;
                    case PoEig::ГРУППА_ЭКОЛОГИЯ:
                        $viewName = '_field_ep';
                        break;
                    default:
                        if ($ei_id == PoEi::СТАТЬЯ_БЛАГОДАРНОСТИ) {
                            $viewName = '_field_ca';
                        }
                        break;
                }
            }

            if (!empty($viewName)) {
                return $this->renderAjax($viewName, [
                    'model' => new Po(),
                    'form' => new \yii\bootstrap\ActiveForm(),
                ]);
            }
        }

        return false;
    }

    /**
     * Выполняет интерактивное изменение статуса платежного ордера на "Оплачено".
     * set-paid-on-the-fly
     * @param $po_id integer идентфикатор платежного ордера
     * @param $state_id integer новый статус, который необходимо присвоить
     * @return mixed
     */
    public function actionSetPaidOnTheFly($po_id, $state_id)
    {
        $state_id = intval($state_id);
        $state = PaymentOrdersStates::findOne($state_id);

        $po_id = intval($po_id);
        $model = Po::findOne($po_id);
        if ($state != null && $model != null) {
            // бухгалтер по бюджету должен обладать правом согласования платежей, проверяется следующим образом:
            if (
                Yii::$app->user->can('accountant_b') &&
                $model->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ &&
                (!in_array($model->ei_id, UsersEiApproved::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])->column()) ||
                    $model->amount > (float)Yii::$app->user->identity->profile->po_maa)
            ) return false;

            $model->state_id = $state_id;
            return $model->save(false);
        }

        return false;
    }

    /**
     * @param $id integer идентификатор привязки
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @return bool
     */
    public function actionDeleteValueLink($id)
    {
        $model = PoPop::findOne($id);
        if ($model) {
            return $model->delete();
        }
    }

    /**
     * Выполняет импорт платежных ордеров из файла Excel, содержащего информацию о выплатах з/п.
     * @return mixed
     */
    public function actionImportSalary()
    {
        $model = new DynamicModel(['ei_id', 'paid_at', 'comment', 'importFile']);
        $model->addRule('ei_id', 'required', ['message' => 'Необходимо выбрать статью расходов']);
        $model->addRule('comment', 'required');
        $model->addRule('importFile', 'required', ['message' => 'Необходимо выбрать файл Excel']);
        $model->addRule(['ei_id', 'paid_at'], 'integer');
        $model->addRule('ei_id', 'exist', ['skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']]);
        $model->addRule('comment', 'string');
        $model->addRule('comment', 'trim');
        $model->addRule('comment', 'default', ['value' => null]);
        $model->addRule('importFile', 'file', ['skipOnEmpty' => false, 'extensions' => ['xls', 'xlsx'], 'checkExtensionByMimeType' => false]);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->importFile = UploadedFile::getInstance($model, 'importFile');
                try {
                    $successCount = 0; // количество успешно импортированных ордеров
                    $data = Excel::import($model->importFile->tempName, ['setFirstRecordAsKeys' => false]);

                    if (isset($data) && count($data) > 0) {
                        // на 13й строчке в ячейке А должна быть цифра 1, обозначающая номер сотрудника по порядку
                        // иначе - формат документа не соответствует

                        // для начала попытаемся извлечь дату оплаты ведомости
                        /*
                        // временно отключено по просьбе заказчика
                        $payDate = trim($data[3]['A']);
                        $payDate = mb_substr($payDate, 9, 4) . '-' . mb_substr($payDate, 6, 2) . '-' . mb_substr($payDate, 3, 2) . ' 04:00:00';
                        $payDate = strtotime($payDate);
                        */

                        $baseRow = 13;
                        while (!empty($data[$baseRow]['A'])) {
                            // для начала необходимо идентифицировать сотрудника по справочнику контрагентов
                            // если сотрудник идентифицирован не будет, то платежный ордер не может быть создан
                            $employeeName = trim($data[$baseRow]['D']);
                            if (!empty($employeeName) && $employeeName != '#NULL!') {
                                $company = Companies::find()->where([
                                    'or',
                                    ['like', 'name', $employeeName],
                                    ['like', 'name_full', $employeeName],
                                    ['like', 'name_short', $employeeName],
                                ])->all();

                                if (count($company) == 1) {
                                    // удаляем спецсимволы и символы, которые могут мешать преобразованию: неразрывный и обычный пробел, запятую
                                    $amount = floatval(str_replace([',', ' ', chr(0xC2) . chr(0xA0)], '', $data[$baseRow]['K']));

                                    $paymentOrder = new Po([
                                        'state_id' => PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ,
                                        'company_id' => $company[0]->id,
                                        'ei_id' => $model->ei_id,
                                        'amount' => $amount,
                                        // могут быть использованы реквизиты:
                                        //'' => ?,
                                        //'fo_project_id' => ?,
                                        'comment' => $model->comment . "\r\nк/с " . $data[$baseRow]['C'],
                                    ]);

                                    if (!empty($model->paid_at)) {
                                        $paymentOrder->state_id = PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН;
                                        // если ордер отмечается оплаченным, то имеет смысл отметить его согласованным
                                        $paymentOrder->approved_at = min(time(), $model->paid_at);
                                        $paymentOrder->paid_at = $model->paid_at;
                                    }

                                    if ($paymentOrder->save()) {
                                        $successCount++;
                                    }
                                    else {
                                        $errors[] = '<p><strong>Строка ' . $baseRow . '</strong>. Ошибка при создании платежного ордера!</p>';
                                        foreach ($paymentOrder->errors as $error) {
                                            $errors[] = $error[0] . '<br />';
                                        }
                                        $errors[] = '<p>&mdash;&mdash;&mdash;</p>';
                                    }
                                }
                                else {
                                    $errors[] = 'В строке ' . $baseRow . ' не идентифицирован сотрудник ' . $employeeName . '.';
                                }
                            }
                            else {
                                break;
                            }

                            $baseRow++;
                        }
                    }

                    if ($successCount > 0) {
                        Yii::$app->session->setFlash('success', 'Успешно импортировано ' . foProjects::declension($successCount, ['ордер','ордера','ордеров']) . '.');
                    }
                    else {
                        Yii::$app->session->setFlash('info', 'Импорт завершился, но ни один платежный ордер не был создан.');
                    }

                    if (!empty($errors))  {
                        $errorMsg = '';
                        foreach ($errors as $error) {
                            $errorMsg .= '<p>' . $error . '</p>';
                        }
                        Yii::$app->session->setFlash('error', $errorMsg);
                    }
                }
                catch (\Exception $exception) {
                    Yii::$app->session->setFlash('error', $exception->getMessage() . '<p>Попробуйте разблокировать файл (кнопка Разрешить редактирование и Сохранить) или же пересохранить файл в формате XLS.</p>');
                }
            }
        }
        else {
            $month = date('m');
            $month--;
            if ($month == 0 ) $month = 12;

            $model->comment = 'з/п ' . mb_strtolower(ReportPoAnalytics::MONTHS_RUSSIAN[$month]);
        }

        return $this->render('import_salary', [
            'model' => $model,
        ]);
    }

    /**
     * Выполняет импорт платежных ордеров из файла Excel, содержащего информацию о налогах на ФОТ.
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionImportWageFund()
    {
        // поля, которые используются как значения свойств в платежном ордере
        $propertiesFields = [
            'Ndfl' => 'НДФЛ',
            'Pf' => 'ПФ',
            'Fss' => 'ФСС',
            'FssNs' => 'ФСС (НС)',
            'Foms' => 'ФОМС',
        ];

        // общие реквизиты динамической модели
        $attributes = ['ei_id', 'paid_at', 'comment', 'importFile'];

        // дополним атрибуты динамической модели обязательными значениями свойств статьи
        foreach ($propertiesFields as $name => $label) {
            $newName = PoValues::IMPORT_WAGE_FUND_FIELD_PREFIX . $name;

            $attributes[] = $newName . '_id'; // идентификатор значения
            $attributes[] = $newName . '_col'; // колонка листа Excel, из которой будет извлекаться значение

            $propertiesFields[$newName] = $label;
            unset($propertiesFields[$name]);
        }

        $model = new DynamicModel($attributes);
        $model->addRule('ei_id', 'required', ['message' => 'Необходимо выбрать статью расходов']);
        $model->addRule('ei_id', 'exist', ['skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']]);

        // сделаем обязательными для заполнения дополнительные атрибуты (значения свойств статьи)
        foreach ($propertiesFields as $name => $label) {
            $model->addRule($name . '_id', 'required', ['message' => 'Необходимо выбрать свойство ' . $label]);
            $model->addRule($name . '_col', 'required', ['message' => 'Необходимо указать колонку ' . $label]);

            $model->addRule($name . '_id', 'exist', ['skipOnError' => true, 'targetClass' => PoValues::class, 'targetAttribute' => [$name . '_id' => 'id']]);
        }

        $model->addRule('comment', 'required');
        $model->addRule('importFile', 'required', ['message' => 'Необходимо выбрать файл Excel']);

        $integerFields = ['ei_id'];
        foreach ($propertiesFields as $name => $label) {
            $integerFields[] = $name;
        }
        $model->addRule($integerFields, 'integer');
        unset($integerFields);

        $model->addRule('comment', 'string');
        $model->addRule('comment', 'trim');
        $model->addRule('comment', 'default', ['value' => null]);
        $model->addRule('importFile', 'file', ['skipOnEmpty' => false, 'extensions' => ['xls', 'xlsx'], 'checkExtensionByMimeType' => false]);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->importFile = UploadedFile::getInstance($model, 'importFile');
                try {
                    $successCount = 0; // количество успешно импортированных ордеров
                    $data = Excel::import($model->importFile->tempName, ['setFirstRecordAsKeys' => false]);

                    if (isset($data) && count($data) > 0) {
                        // идентифицируем значения свойств
                        $valuesModels = []; // модели значений свойств (для идентификации их свойств - родительских элементов)
                        $valuesIds = [];
                        foreach ($propertiesFields as $name => $label) {
                            $valuesIds[] = $model->{$name . '_id'};
                        }
                        if (count($valuesIds) > 0) {
                            // по этому массиву будем определять свойства значений свойств (родительские элементы)
                            // родительские элементя обязательны для заполнения в таблице PoPop
                            $valuesModels = ArrayHelper::map(PoValues::find()->where(['id' => $valuesIds])->asArray()->all(), 'id', 'property_id');
                        }
                        unset($valuesIds);

                        if (count($valuesModels) == 0) {
                            $errors[] = 'Невозможно идентифицировать значения свойств статьи расходов.';
                        }
                        else {
                            $baseRow = 10;
                            while (!empty($data[$baseRow]['A']) && false === mb_stripos($data[$baseRow]['A'], 'Итого')) {
                                // для начала необходимо идентифицировать сотрудника по справочнику контрагентов
                                // если сотрудник идентифицирован не будет, то платежный ордер не может быть создан
                                $employeeName = trim($data[$baseRow]['A']);
                                if (!empty($employeeName) && $employeeName != '#NULL!') {
                                    $company = Companies::find()->where([
                                        'or',
                                        ['like', 'name', $employeeName],
                                        ['like', 'name_full', $employeeName],
                                        ['like', 'name_short', $employeeName],
                                    ])->all();

                                    if (count($company) == 1) {
                                        // контрагент идентифицирован и идентифицирован однозначно

                                        foreach ($propertiesFields as $name => $label) {
                                            // признак успешного выполнения операции по отдельному ордеру
                                            $currentSuccess = true;
                                            $transaction = Yii::$app->db->beginTransaction();
                                            try {
                                                // удаляем спецсимволы и символы, которые могут мешать преобразованию: неразрывный и обычный пробел, запятую
                                                $amount = floatval(str_replace([',', ' ', chr(0xC2) . chr(0xA0)], '', $data[$baseRow][$model->{$name . '_col'}]));

                                                $paymentOrder = new Po([
                                                    'state_id' => PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ,
                                                    'company_id' => $company[0]->id,
                                                    'ei_id' => $model->ei_id,
                                                    'amount' => $amount,
                                                    'comment' => str_replace('%TAX_NAME%', $label, $model->comment),
                                                ]);

                                                if (!empty($model->paid_at)) {
                                                    // если пользователь указывает дату оплаты, то применим ее и сделаем ордер оплаченным
                                                    $paymentOrder->state_id = PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН;
                                                    // если ордер отмечается оплаченным, то имеет смысл отметить его согласованным
                                                    $paymentOrder->approved_at = min(time(), $model->paid_at);
                                                    $paymentOrder->paid_at = $model->paid_at;
                                                }

                                                if ($paymentOrder->save()) {
                                                    // платежный ордер успешо сохранен, дополним его свойствами
                                                    $poPropertiesValues = new PoPop([
                                                        'po_id' => $paymentOrder->id,
                                                        'ei_id' => $model->ei_id,
                                                        'property_id' => ArrayHelper::getValue($valuesModels, $model->{$name . '_id'}),
                                                        'value_id' => $model->{$name . '_id'},
                                                    ]);
                                                    if ($poPropertiesValues->save()) {
                                                        $successCount++;
                                                    } else {
                                                        $currentSuccess = false;
                                                    }
                                                    unset($poPropertiesValues);
                                                } else {
                                                    $currentSuccess = false;

                                                    $errors[] = '<p><strong>Строка ' . $baseRow . '</strong>. Ошибка при создании платежного ордера!</p>';
                                                    foreach ($paymentOrder->errors as $error) {
                                                        $errors[] = $error[0] . '<br />';
                                                    }
                                                    $errors[] = '<p>&mdash;&mdash;&mdash;</p>';
                                                }

                                                $currentSuccess ? $transaction->commit() : $transaction->rollBack();
                                            } catch (\Exception $e) {
                                                $transaction->rollBack();
                                                throw $e;
                                            } catch (\Throwable $e) {
                                                $transaction->rollBack();
                                                throw $e;
                                            }
                                        }
                                    }
                                    else {
                                        $errors[] = 'В строке ' . $baseRow . ' не идентифицирован сотрудник ' . $employeeName . '.';
                                    }
                                }

                                $baseRow++;
                            }
                        }
                    }

                    if ($successCount > 0) {
                        Yii::$app->session->setFlash('success', 'Успешно импортировано ' . foProjects::declension($successCount, ['ордер','ордера','ордеров']) . '.');
                    }
                    else {
                        Yii::$app->session->setFlash('info', 'Импорт завершился, но ни один платежный ордер не был создан.');
                    }

                    if (!empty($errors))  {
                        $errorMsg = '';
                        foreach ($errors as $error) {
                            $errorMsg .= '<p>' . $error . '</p>';
                        }
                        Yii::$app->session->setFlash('error', $errorMsg);
                    }
                }
                catch (\Exception $exception) {
                    Yii::$app->session->setFlash('error', $exception->getMessage() . '<p>Попробуйте разблокировать файл (кнопка Разрешить редактирование и Сохранить) или же пересохранить файл в формате XLS.</p>');
                }
            }

            //return $this->redirect(PoController::ROOT_URL_AS_ARRAY);
        }
        else {
            $model->ei_id = PoEig::ГРУППА_НАЛОГИ;

            // значения по умолчанию
            $model->propNdfl_id = PoValues::VALUE_НДФЛ;
            $model->propNdfl_col = 'F';

            $model->propPf_id = PoValues::VALUE_ПФ;
            $model->propPf_col = 'G';

            $model->propFss_id = PoValues::VALUE_ФСС;
            $model->propFss_col = 'J';

            $model->propFssNs_id = PoValues::VALUE_ФСС_НС;
            $model->propFssNs_col = 'K';

            $model->propFoms_id = PoValues::VALUE_ФОМС;
            $model->propFoms_col = 'L';

            $month = date('m');
            $year = Yii::$app->formatter->asDate(time(), 'php:Y');
            $month--;
            if ($month <= 0) {
                $month = 12;
                $year--;
            }
            $year .= ' г.';

            $model->comment = 'Отчисление c ФОТ на уплату %TAX_NAME% за ' . mb_strtolower(ReportPoAnalytics::MONTHS_RUSSIAN[$month]) . ' ' . $year;
        }

        return $this->render('import_wage_fund', [
            'model' => $model,
            'propertiesFields' => $propertiesFields,
            'amProperties' => \common\models\PoValues::arrayMapByGroupsForSelect2(),
        ]);
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = PoFiles::getUploadsFilepath($obj_id);
        if ($upload_path === false) return 'Невозможно создать папку для хранения загруженных файлов!';

        // массив загружаемых файлов
        $files = $_FILES['files'];
        // массив имен загружаемых файлов
        $filenames = $files['name'];
        if (count($filenames) > 0)
            for ($i=0; $i < count($filenames); $i++) {
                // идиотское действие, но без него
                // PHP Strict Warning: Only variables should be passed by reference
                $tmp = explode('.', basename($filenames[$i]));
                $ext = end($tmp);
                $filename = mb_strtolower(Yii::$app->security->generateRandomString() . '.' . $ext, 'utf-8');
                $filepath = $upload_path . '/' . $filename;
                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $fu = new PoFiles();
                    $fu->po_id = $obj_id;
                    $fu->ffp = $filepath;
                    $fu->fn = $filename;
                    $fu->ofn = $filenames[$i];
                    $fu->size = filesize($filepath);
                    if ($fu->validate()) $fu->save(); else return 'Загруженные данные неверны.';
                };
            };

        return [];
    }

    /**
     * Отдает на скачивание файл, на который позиционируется по идентификатору из параметров.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDownloadFile($id)
    {
        if (is_numeric($id)) if ($id > 0) {
            $model = PoFiles::findOne($id);
            if (file_exists($model->ffp))
                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Выполняет предварительный показ изображения.
     */
    public function actionPreviewFile($id)
    {
        $model = PoFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage()) {
                return \yii\helpers\Html::img(PoFiles::getUploadsFilepath($model->po_id, true) . '/' . $model->fn, ['width' => '100%']);
            }
            else {
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_FILE, 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
            }
        }

        return false;
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param $id
     * @return Response
     * @throws NotFoundHttpException если файл не будет обнаружен
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFile($id)
    {
        $model = PoFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->po_id;
            $model->delete();

            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Рендерит поле с причиной отказа.
     * @return mixed
     */
    public function actionRenderFieldReason()
    {
        return $this->renderAjax('_field_reject_reason', ['model' => new Po()]);
    }
}
