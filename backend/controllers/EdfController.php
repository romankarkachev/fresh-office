<?php

namespace backend\controllers;

use Yii;
use common\models\DangerClasses;
use common\models\DocumentsTypes;
use common\models\ContractTypes;
use common\models\Edf;
use common\models\EdfSearch;
use common\models\EdfTp;
use common\models\EdfTpSearch;
use common\models\EdfFiles;
use common\models\EdfFilesSearch;
use common\models\EdfStates;
use common\models\EdfStatesHistorySearch;
use common\models\EdfTemplates;
use common\models\EdfTemplatesSearch;
use common\models\CorrespondencePackages;
use common\models\CorrespondencePackagesStates;
use common\models\EdfFillFkkoBasisForm;
use common\models\ProjectsStates;
use common\models\TransportRequests;
use common\models\TransportRequestsWaste;
use common\models\FinishEdfForm;
use common\models\FileStorage;
use common\models\foCompanyDetails;
use common\models\foGoods;
use common\models\foCompanyGoods;
use common\models\foUnits;
use common\models\Fkko;
use common\models\foCompany;
use common\models\UploadingFilesMeanings;
use common\models\EdfDialogs;
use common\models\EdfDialogsSearch;
use common\models\LicensesRequests;
use common\models\LicensesRequestsFkko;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use moonland\phpexcel\Excel;

/**
 * Контроллер документооборота.
 * EdfController implements the CRUD actions for Edf model.
 */
class EdfController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'edf';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Делопроизводство';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования электронного документа
     */
    const UPDATE_URL = 'update';

    /**
     * URL для подбора отходов по коду ФККО
     */
    const FKKO_LIST_FOR_TYPEAHEAD_URL = 'list-of-fkko-for-typeahead';
    const FKKO_LIST_FOR_TYPEAHEAD_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::FKKO_LIST_FOR_TYPEAHEAD_URL];

    /**
     * URL для обработки выбранного пользователем кода ФККО
     */
    const FKKO_ONCHANGE_URL = 'fkko-onchange';
    const FKKO_ONCHANGE_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::FKKO_ONCHANGE_URL];

    /**
     * URL для автоматического заполнения реквизитов при выборе родительского документа
     */
    const PARENT_ONCHANGE_URL = 'parent-onchange';
    const PARENT_ONCHANGE_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::PARENT_ONCHANGE_URL];

    /**
     * URL для загрузки файлов через ajax
     */
    const UPLOAD_FILES_URL = 'upload-files';
    const UPLOAD_FILES_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::UPLOAD_FILES_URL];

    /**
     * URL для скачивания приаттаченных файлов
     */
    const DOWNLOAD_FILE_URL = 'download-file';

    /**
     * URL для предварительного просмотра файлов через ajax
     */
    const PREVIEW_FILE_URL = 'preview-file';
    const PREVIEW_FILE_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::PREVIEW_FILE_URL];

    /**
     * URL для удаления файла через ajax
     */
    const DELETE_FILE_URL = 'delete-file';
    const DELETE_FILE_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::DELETE_FILE_URL];

    /**
     * URL для удаления нескольких выделенных файлов через ajax
     */
    const DELETE_FEW_FILES_URL = 'delete-few-files';
    const DELETE_FEW_FILES_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::DELETE_FEW_FILES_URL];

    /**
     * URL для удаления файлов через ajax
     */
    const GENERATE_FROM_TEMPLATE_URL = 'generate-from-template';
    const GENERATE_FROM_TEMPLATE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::GENERATE_FROM_TEMPLATE_URL];

    /**
     * URL для рендеринга формы заполнения табличной части на основании запроса на транспорт или запроса лицензий
     */
    const FILL_FKKO_TR_BASIS_URL = 'fill-fkko-tr-basis';
    const FILL_FKKO_TR_BASIS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::FILL_FKKO_TR_BASIS_URL];

    /**
     * URL для отображения закрытия документа и помещения отмеченных файлов в хранилище
     */
    const URL_FINISH_EDF = 'finish-edf';
    const URL_FINISH_EDF_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_FINISH_EDF];

    /**
     * URL для отправки реквизитов контрагента в CRM Fresh Office
     */
    const URL_PUSH_TO_FRESH = 'push-to-fresh-office';
    const URL_PUSH_TO_FRESH_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PUSH_TO_FRESH];

    /**
     * URL для отправки товаров контрагента в CRM Fresh Office
     */
    const URL_PULL_GOODS_TO_FRESH = 'pull-goods-to-fresh-office';
    const URL_PULL_GOODS_TO_FRESH_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PULL_GOODS_TO_FRESH];

    /**
     * URL для рендеринга списка сообщений диалогов при помощи ajax
     */
    const DIALOGS_MESSAGES_LIST_URL = 'dialog-messages-list';
    const DIALOGS_MESSAGES_LIST_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::DIALOGS_MESSAGES_LIST_URL];

    /**
     * URL для добавления сообщения в диалоги через ajax
     */
    const ADD_DIALOG_MESSAGE_URL = 'add-dialog-message';
    const ADD_DIALOG_MESSAGE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::ADD_DIALOG_MESSAGE_URL];

    /**
     * URL для подбора файлов из файлового хранилища
     */
    const LIST_FS_TYPEAHEAD_URL = 'list-of-storage-files-typeahead';
    const LIST_FS_TYPEAHEAD_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::LIST_FS_TYPEAHEAD_URL];

    /**
     * URL для добавления файлов из файлового хранилища в электронный документ
     */
    const ADD_FILE_FROM_STORAGE_URL = 'add-file-from-storage';
    const ADD_FILE_FROM_STORAGE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::ADD_FILE_FROM_STORAGE_URL];

    /**
     * URL для скачивания выделенных пользователем файлов одним архивом
     */
    const URL_DOWNLOAD_SELECTED_FILES = 'download-selected-files';
    const URL_DOWNLOAD_SELECTED_FILES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_SELECTED_FILES];

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
                        'actions' => [self::DOWNLOAD_FILE_URL],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', self::UPDATE_URL,
                            'render-fields',
                            self::FKKO_LIST_FOR_TYPEAHEAD_URL, self::FKKO_ONCHANGE_URL, self::PARENT_ONCHANGE_URL,
                            'render-fkko-row', 'delete-fkko-row',
                            self::UPLOAD_FILES_URL, self::PREVIEW_FILE_URL, self::DELETE_FILE_URL, self::DELETE_FEW_FILES_URL,
                            self::GENERATE_FROM_TEMPLATE_URL, self::FILL_FKKO_TR_BASIS_URL, self::URL_FINISH_EDF,
                            self::URL_PUSH_TO_FRESH, self::URL_PULL_GOODS_TO_FRESH,
                            self::DIALOGS_MESSAGES_LIST_URL, self::ADD_DIALOG_MESSAGE_URL,
                            self::LIST_FS_TYPEAHEAD_URL, self::ADD_FILE_FROM_STORAGE_URL, self::URL_DOWNLOAD_SELECTED_FILES,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_manager', 'operator_head', 'edf', 'ecologist', 'ecologist_head', 'tenders_manager'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    self::DELETE_FEW_FILES_URL => ['POST'],
                    self::ADD_FILE_FROM_STORAGE_URL => ['POST'],
                ],
            ],
        ];
    }

    /*
    // закрываем доступ всем пользователям в этот раздел (если временно необходимо)
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        //return true;
        if (Yii::$app->user->can('root')) {
            return true;
        }
        else {
            Yii::$app->session->setFlash('info', 'Доступ к модулю "Делопроизводство" временно запрещен.');
            Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());

            return false;
        }
    }
    */

    /**
     * Делает выборку сообщений в документообороте.
     * @param $model Edf
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchDialogs($model)
    {
        $searchModel = new EdfDialogsSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'ed_id' => $model->id,
            ]
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Делает выборку табличной части электронного документа.
     * @param $model Edf
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchTp($model)
    {
        $searchModel = new EdfTpSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['ed_id' => $model->id]]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Делает выборку файлов, приаттаченных к электронному документу.
     * @param $parent_id integer идентификатор родительского объекта, приаттаченные файлы которого рендерятся
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchFiles($parent_id)
    {
        $searchModel = new EdfFilesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['ed_id' => $parent_id]]);
        $dataProvider->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Рендерит список файлов, прикрепленных к электронному документу.
     * @param $parent_id integer идентификатор родительского объекта, приаттаченные файлы которого рендерятся
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderFiles($parent_id)
    {
        return $this->renderAjax('_files', [
            'dataProvider' => $this->fetchFiles($parent_id),
        ]);
    }

    /**
     * Делает выборку истории изменений статусов, приаттаченных к электронному документу.
     * @param $model Edf
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchHistory($model)
    {
        $searchModel = new EdfStatesHistorySearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['ed_id' => $model->id]]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Делает выборку шаблонов, которые должны генерироваться к документам типа, переданного в параметрах.
     * @param integer $dt_id тип документа, шаблоны которого необходимо извлечь
     * @param integer $ct_id тип договора для уточнения типа документа
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchTemplates($dt_id, $ct_id=null)
    {
        $searchModel = new EdfTemplatesSearch();
        $conditions = [
            $searchModel->formName() => [
                'type_id' => $dt_id,
            ]
        ];

        if (!empty($ct_id)) {
            $conditions[$searchModel->formName()]['ct_id'] = $ct_id;
        }

        $dataProvider = $searchModel->search($conditions);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Lists all Edf models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EdfSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(self::ROOT_URL_AS_ARRAY);
                return false;
            }

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Список электронных документов (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => [
                    [
                        'attribute' => 'created_at',
                        'label' => 'Создан',
                        'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                    ],
                    [
                        'attribute' => 'managerProfileName',
                        'visible' => !Yii::$app->user->can('sales_department_manager'),
                    ],
                    'stateName',
                    [
                        'attribute' => 'stateChangedAt',
                        'label' => 'Статус приобретен',
                        'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                    ],
                    'typeName',
                    'contractTypeName',
                    'req_name_short',
                    'organizationName',
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $isFilterDs = false; // признак применения отбора по типу документа, и это допсоглашение
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            if (!empty($searchModel->type_id) && $searchModel->type_id == DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ) {
                $isFilterDs = true;
            }

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => !empty(Yii::$app->request->get($searchModel->formName())),
                'queryString' => $queryString,
                'isFilterDs' => $isFilterDs,
            ]);
        }
    }

    /**
     * Creates a new Edf model.
     * If creation is successful, the browser will be redirected to the 'update' page.
     * @return mixed
     * @throws \Throwable
     */
    public function actionCreate()
    {
        $hasAccess = Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('ecologist') || Yii::$app->user->can('ecologist_head');
        $model = new Edf();

        if ($model->load(Yii::$app->request->post())) {
            // пришедшие снаружи идентификаторы переводим в модели строк табличной части "Отходы"
            $postTp = $model->makeTpModelsFromPostArray();

            $model->initialFiles = UploadedFile::getInstances($model, 'initialFiles');

            switch ($model->type_id) {
                case DocumentsTypes::TYPE_ДОГОВОР:
                    $model->scenario = 'creatingContract';
                    break;
                case DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ:
                    //$model->scenario = 'creatingAgreement';
                    break;
            }

            if ($model->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save(false)) {
                        // записываем заново табличную часть "Отходы"
                        $successWaste = true;
                        foreach ($postTp as $tp) {
                            $row = new EdfTp();
                            $row->attributes = $tp->attributes;
                            $row->ed_id = $model->id;
                            if (!$row->save()) {
                                $successWaste = false;
                                $details = '';
                                foreach ($row->errors as $error)
                                    foreach ($error as $detail)
                                        $details .= '<p>' . $detail . '</p>';

                                Yii::$app->getSession()->setFlash('error', $details);
                                break;
                            }
                        }

                        if ($successWaste) {
                            $transaction->commit();
                            $successFiles = false;
                            if (count($model->initialFiles) > 0) {
                                $files = $model->upload();
                                if (false === $files) {
                                    Yii::$app->session->setFlash('error', 'Не удалось загрузить файлы.');
                                } else $successFiles = true;
                            } else $successFiles = true;

                            if ($successFiles) {
                                return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::UPDATE_URL, 'id' => $model->id]);
                            }
                        }

                        $transaction->rollBack();
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        } else {
            $model->doc_date_expires = date('Y-m-d', strtotime("+11 months")); // плюс 11 месяцев от текущей даты
            $model->is_typical_form = true;
            $model->state_id = EdfStates::STATE_ЧЕРНОВИК;
            $model->basis = 'Устава';
            if ($hasAccess) {
                $model->type_id = DocumentsTypes::TYPE_ДОГОВОР;
                $model->manager_id = Yii::$app->user->id;
            }
            $postTp = [];
        }

        return $this->render('create', [
            'model' => $model,
            'tp' => $postTp,
            'hasAccess' => $hasAccess,
        ]);
    }

    /**
     * Updates an existing Edf model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $hasAccess = Yii::$app->user->can('sales_department_manager');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // фиксируем действительный статус, чтобы вернуться к нему в случае, если валидация будет провалена с треском
            $state_id = $model->state_id;

            // пришедшие снаружи идентификаторы переводим в модели строк табличной части
            $postTp = $model->makeTpModelsFromPostArray();

            // если нажата кнопка "Отправить на формирование"
            if (Yii::$app->request->post('create_order') !== null) $model->state_id = EdfStates::STATE_ЗАЯВКА;

            // если нажата кнопка "Отправить на согласование"
            if (Yii::$app->request->post('approve_request') !== null) {
                $model->scenario = 'approvingDocument';
                $model->state_id = EdfStates::STATE_СОГЛАСОВАНИЕ;
            }

            // если нажата кнопка "Утверждено"
            if (Yii::$app->request->post('approved') !== null) {
                $model->state_id = EdfStates::STATE_УТВЕРЖДЕНО;
            }

            // если нажата кнопка "Вернуть"
            if (Yii::$app->request->post('rollback') !== null) {
                $model->state_id = EdfStates::STATE_СОГЛАСОВАНИЕ;
            }

            // если нажата кнопка "Отдать на подпись"
            if (Yii::$app->request->post('director_signs') !== null) {
                $model->state_id = EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА;
            }

            // если нажата кнопка "На подписи у заказчика"
            if (Yii::$app->request->post('customer_signs') !== null) {
                $model->state_id = EdfStates::STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА;
            }

            // если нажата кнопка "Отказать"
            if (Yii::$app->request->post('reject') !== null) {
                $model->state_id = EdfStates::STATE_ОТКАЗ;
            }

            // если нажата кнопка "Отказ клиента"
            if (Yii::$app->request->post('customer_reject') !== null) {
                $model->state_id = EdfStates::STATE_ОТКАЗ_КЛИЕНТА;
            }

            // если нажата кнопка "Завершено"
            if (Yii::$app->request->post('finish_edf') !== null) {
                $model->state_id = EdfStates::STATE_ЗАВЕРШЕНО;
            }

            if ($model->validate() && $model->save(false)) {
                $successWaste = true;

                // если нажата кнопка "Создать пакет корреспонденции"
                if (null !== Yii::$app->request->post('create_cp') || null !== Yii::$app->request->post('sign_wcp')) {
                    $success = false;
                    if (null !== Yii::$app->request->post('create_cp')) {
                        $cp = new CorrespondencePackages([
                            'is_manual' => true,
                            'cps_id' => CorrespondencePackagesStates::STATE_ЧЕРНОВИК,
                            'fo_id_company' => $model->fo_ca_id,
                            'customer_name' => TransportRequests::getCustomerName($model->fo_ca_id),
                            'state_id' => ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
                            'manager_id' => $model->manager_id,
                        ]);
                        $cp->pad = $cp->convertPadTableToArray();
                        $cp->scenario = 'manual_creating';
                        if ($cp->save()) {
                            $success = true;
                            $model->updateAttributes([
                                'cp_id' => $cp->id,
                            ]);
                        }
                    }
                    else {
                        // требуется только лишь отметить документ подписанным (без создания пакета корреспонденции)
                        $success = true;
                    }

                    if ($success) {
                        $model->updateAttributes([
                            'state_id' => EdfStates::STATE_ПОДПИСАН_РУКОВОДСТВОМ,
                        ]);
                        // переводим пользователя в созданный пакет корреспонденции, если это требовалось
                        if (null !== Yii::$app->request->post('create_cp') && null !== $cp) {
                            return $this->redirect(['/correspondence-packages/update', 'id' => $cp->id]);
                        }
                    }
                    else {
                        $successWaste = false;
                    }
                    unset($success);
                }

                // записываем заново табличную часть "Отходы"
                EdfTp::deleteAll(['ed_id' => $model->id]);

                foreach ($postTp as $tp) {
                    $row = new EdfTp();
                    $row->attributes = $tp->attributes;
                    if (!$row->save()) {
                        $successWaste = false;
                        $details = '';
                        foreach ($row->errors as $error)
                            foreach ($error as $detail)
                                $details .= '<p>' . $detail . '</p>';

                        Yii::$app->getSession()->setFlash('error', $details);
                        break;
                    }
                }

                if ($successWaste) return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::UPDATE_URL, 'id' => $model->id]);
            }
            else {
                $model->state_id = $state_id;
            }
        }
        else {
            // если последнее сообщение в диалоги добавлено не текущим пользователем, отметим все сообщения как прочитанные
            $lastMsg = EdfDialogs::find()->where(['ed_id' => $id])->orderBy('`created_at` DESC')->one();
            if ($lastMsg && $lastMsg->created_by != Yii::$app->user->id) {
                EdfDialogs::updateAll([
                    'read_at' => time(),
                ], [
                    'ed_id' => $id,
                    'read_at' => null,
                ]);
            }

            // табличная часть с отходами
            $postTp = EdfTp::find()->where(['ed_id' => $model->id])->all();
            /*
             * // решено предоставлять доступ к табличной части всегда и всем
            if ((!$hasAccess && $model->state_id < EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА) || ($hasAccess && $model->state_id == EdfStates::STATE_СОГЛАСОВАНИЕ))  {
                // табличную часть можно редактировать
                $postTp = EdfTp::find()->where(['ed_id' => $model->id])->all();
            }
            else {
                // табличная часть для вывода просто как список (когда документ приобрел определенный статус или редактирование запрещено для роли)
                $postTp = $this->fetchTp($model);
            }
            */
        }

        return $this->render('update', [
            'model' => $model,
            'tp' => $postTp,
            'hasAccess' => $hasAccess,
            'dpDialogs' => $this->fetchDialogs($model),
            'dpFiles' => $this->fetchFiles($id),
            'dpHistory' => $this->fetchHistory($model),
        ]);
    }

    /**
     * Deletes an existing Edf model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the Edf model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Edf the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Edf::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Формирует поля для выбранного пользователем элемента меню.
     * render-fields-org
     * @param integer $kind определяет значение в каком именно поле изменилось
     * @param $value integer новое значение
     * @param $ca_id integer контрагент
     * @return mixed
     */
    public function actionRenderFields($kind, $value)
    {
        if (Yii::$app->request->isAjax) {
            $kind = intval($kind);
            $value = intval($value);
            $ca_id = 0;
            if (null !== Yii::$app->request->get('ca_id')) $ca_id = intval(Yii::$app->request->get('ca_id'));
            if ($kind > 0 && $value > 0) {
                $model = null;
                $formName = '';
                switch ($kind) {
                    case 1:
                        $model = new Edf([
                            'type_id' => $value,
                        ]);
                        if (!empty($ca_id)) {
                            $model->fo_ca_id = $ca_id;
                        }
                        $formName = '_field_dt';
                        break;
                    case 2:
                        $model = new Edf([
                            'org_id' => $value,
                        ]);
                        if (null !== Yii::$app->request->get('type_id')) $model->type_id = intval(Yii::$app->request->get('type_id'));

                        // номер документа вычисляем только для договоров
                        if ($model->type_id == DocumentsTypes::TYPE_ДОГОВОР) $model->calcNextDocumentNumber();

                        if (count($model->organization->bankAccounts) > 0) {
                            // выбираем первый попавшийся счет
                            $model->ba_id = $model->organization->bankAccounts[0]->id;
                        }
                        $formName = '_fields_org';
                        break;
                }

                if (!empty($formName) && $model) return $this->renderAjax($formName, [
                    'model' => $model,
                    'form' => \yii\bootstrap\ActiveForm::begin(),
                ]);
            }
        }

        return '';
    }

    /**
     * Функция выполняет поиск отходов по коду ФККО и наименованию от значения переданного в параметрах.
     * Для виджетов Typeahead.
     * @param $q string
     * @param $counter integer|null
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionListOfFkkoForTypeahead($q, $counter = null)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $query = Fkko::find()->select([
                'id',
                'value' => 'CONCAT(fkko_code, " - ", fkko_name)',
                $counter . ' AS `counter`',
                'fkko_code',
            ])->andFilterWhere([
                'or',
                ['like', 'fkko_code', $q],
                ['like', 'fkko_name', $q],
            ]);

            $fkkos = $query->asArray()->all();
            foreach ($fkkos as $index => $fkko) {
                // определим классы опасности подобранных кодов ФККО
                $fkkos[$index]['dc_id'] = substr(trim($fkko['fkko_code']), -1);
            }

            return $fkkos;
        }
    }

    /**
     * Выполняет идентификацию кода ФККО, выбранного пользователем и подыскивает использованные ранее способ обращения и единицу измерения.
     * @param $fkko_id integer идентификатор кода ФККО, последнее использование которого необходимо найти
     * @return mixed
     */
    public function actionFkkoOnchange($fkko_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $edfTp = EdfTp::find()->joinWith(['ed'])->where(['fkko_id' => $fkko_id])->orderBy('`created_at` DESC')->one();
        if ($edfTp) {
            return [
                'unit_id' => $edfTp->unit_id,
                'hk_id' => $edfTp->hk_id,
            ];
        }

        return false;
    }

    /**
     * Позиционируется на родительский документ и возвращает его некоторые свойства.
     * @param $parent_id integer родительский документ, на основании которого необходимо заполнить текущий
     * @return mixed
     */
    public function actionParentOnchange($parent_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Edf::findOne($parent_id);
        if ($model) {
            return [
                'org_id' => $model->org_id,
                'ca_id' => $model->fo_ca_id,
                'caName' => (foCompany::findOne($model->fo_ca_id))->COMPANY_NAME,
                'inn' => $model->req_inn,
                'kpp' => $model->req_kpp,
                'bank_bn' => $model->req_bn,
                'bank_an' => $model->req_an,
                'bank_bik' => $model->req_bik,
                'bank_ca' => $model->req_ca,
            ];
        }

        return false;
    }

    /**
     * Возвращает результат рендера строки табличной части.
     * @param $counter integer
     * @return string
     */
    public function actionRenderFkkoRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            $model = new EdfTp();
            $edf = new Edf();

            return $this->renderAjax('_row_fkko', [
                'edf' => $edf,
                'model' => $model,
                'counter' => $counter + 1,
            ]);
        }
    }

    /**
     * Выполняет удаление строки табличной части из документа.
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteFkkoRow($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($id != null) {
            EdfTp::deleteAll(['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = EdfFiles::getUploadsFilepath($obj_id);
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
                $filename = mb_strtolower(Yii::$app->security->generateRandomString() . '.'.$ext, 'utf-8');
                $filepath = $upload_path . '/' . $filename;
                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $fu = new EdfFiles();
                    $fu->ed_id = $obj_id;
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
            $model = EdfFiles::findOne($id);
            if (file_exists($model->ffp))
                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param $id
     * @return Response
     * @throws NotFoundHttpException если файл не будет обнаружен
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteFile($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = EdfFiles::findOne($id);
            if ($model) {
                $parent_id = $model->ed_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderFiles($parent_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }

        /*
        $model = EdfFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->ed_id;
            $model->delete();

            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::UPDATE_URL, 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
        */
    }

    /**
     * Удаляет несколько файлов, которые выделил пользователь.
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFewFiles()
    {
        if (Yii::$app->request->post('ids') !== null) {
            $model = null;
            $ids = Yii::$app->request->post('ids');
            $files = EdfFiles::find()->where(['id' => $ids])->all();
            foreach ($files as $file) {
                if (empty($model)) {
                    $model = $file->ed_id;
                }
                $file->delete();
            }

            return $this->renderFiles($model);
            //return $this->renderAjax('_files', ['dataProvider' => $this->fetchFiles($model)]);
        }
    }

    /**
     * Выполняет предварительный показ изображения.
     */
    public function actionPreviewFile($id)
    {
        $model = EdfFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage()) {
                $folder = Yii::getAlias('@uploads-edf') . '/' . str_replace(EdfFiles::getUploadsFilepath(), '', $model->ed->files_full_path);
                return \yii\helpers\Html::img($folder . '/' . $model->fn, ['width' => '100%']);
            }
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::DOWNLOAD_FILE_URL, 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Рендерит форму генерации документов из шаблонов либо производит саму генерацию отмеченных пользователем документов.
     * generate-from-template
     * @return mixed
     * @throws NotFoundHttpException если вдруг по какой-то причине только что созданный из шаблона файл не будет обнаружен
     */
    public function actionGenerateFromTemplate()
    {
        if (Yii::$app->request->isPost) {
            $model = $this->findModel(intval(Yii::$app->request->post('id')));
            $ids = Yii::$app->request->post('tmpl_ids');

            $templates = EdfTemplates::find()->where(['id' => $ids])->all();
            foreach ($templates as $template) {
                if (!file_exists($template->ffp)) continue;

                $org_dir_post_of = $model->dirPostDeclension($model->organization->dir_post);
                $req_dir_post_of = $model->dirPostDeclension($model->req_dir_post);

                $array_subst = [
                    '%CASE_DOC_NUM%' => ($model->type_id != DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ ? $model->doc_num : (!empty($model->parent) ? $model->parent->doc_num : '')),
                    '%CASE_DOC_DATE%' => ($model->type_id != DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ ? Yii::$app->formatter->asDate($model->doc_date, 'php:«d» F Y г.') : (!empty($model->parent) ? Yii::$app->formatter->asDate($model->parent->doc_date, 'php:«d» F Y г.') : '')),

                    '%DOC_NUM%' => $model->doc_num,
                    '%DOC_DATE%' => Yii::$app->formatter->asDate($model->doc_date, 'php:«d» F Y г.'),
                    '%DOC_DATE_EXPIRES%' => (!empty($model->doc_date_expires) ? chr(0xC2).chr(0xA0) . Yii::$app->formatter->asDate($model->doc_date_expires, 'php:«d» F Y г.') : '.'),

                    '%ROOT_DOC_NUM%' => (!empty($model->parent) ? $model->parent->doc_num : ''),
                    '%ROOT_DOC_DATE%' => (!empty($model->parent) ? Yii::$app->formatter->asDate($model->parent->doc_date, 'php:«d» F Y г.') : ''),

                    '%ORG_NAME_FULL%' => $model->organization->name_full . ($model->org_id == 4 ? ' (далее - ' . $model->organization->name_short . ')' : ''),
                    '%ORG_NAME_SHORT%' => $model->organization->name_short,
                    '%ORG_OGRN%' => $model->organization->ogrn,
                    '%ORG_INN%' => $model->organization->inn,
                    '%ORG_KPP%' => $model->organization->kpp,
                    '%ORG_ADDRESS_J%' => $model->organization->address_j,
                    '%ORG_ADDRESS_F%' => $model->organization->address_f,
                    '%ORG_BANK_AN%' => $model->bankAccount->bank_an,
                    '%ORG_BANK_BN%' => $model->bankAccount->bank_name,
                    '%ORG_BANK_CA%' => $model->bankAccount->bank_ca,
                    '%ORG_BANK_BIK%' => $model->bankAccount->bank_bik,
                    '%ORG_PHONES%' => $model->organization->phones,
                    '%ORG_EMAIL%' => $model->organization->email,
                    '%ORG_DIR_POST%' => $model->organization->dir_post,
                    '%ORG_DIR_POST_OF%' => $org_dir_post_of,
                    '%ORG_DIR_NAME%' => $model->organization->dir_name,
                    '%ORG_DIR_NAME_OF%' => (!empty($org_dir_post_of) ? chr(0xC2).chr(0xA0) : '') . $model->organization->dir_name_of,
                    '%ORG_DIR_NAME_SHORT%' => $model->organization->dir_name_short,
                    '%ORG_LICENSE_REQ%' => (!empty($model->organization->license_req) ? chr(0xC2).chr(0xA0) . $model->organization->license_req : '.'),

                    '%CA_NAME_FULL%' => $model->req_name_full,
                    '%CA_NAME_SHORT%' => $model->req_name_short,
                    '%CA_BASIS%' => $model->basis,
                    '%CA_OGRN%' => $model->req_ogrn,
                    '%CA_INN%' => $model->req_inn,
                    '%CA_KPP%' => $model->req_kpp,
                    '%CA_ADDRESS_J%' => $model->req_address_j,
                    '%CA_ADDRESS_F%' => $model->req_address_f,
                    '%CA_BANK_AN%' => $model->req_an,
                    '%CA_BANK_BN%' => $model->req_bn,
                    '%CA_BANK_CA%' => $model->req_ca,
                    '%CA_BANK_BIK%' => $model->req_bik,
                    '%CA_PHONES%' => $model->req_phone,
                    '%CA_EMAIL%' => $model->req_email,
                    '%CA_DIR_POST%' => $model->req_dir_post,
                    '%CA_DIR_POST_OF%' => $req_dir_post_of,
                    '%CA_DIR_NAME%' => $model->req_dir_name,
                    '%CA_DIR_NAME_OF%' => (!empty($req_dir_post_of) ? chr(0xC2).chr(0xA0) : '') . $model->req_dir_name_of,
                    '%CA_DIR_NAME_SHORT%' => $model->req_dir_name_short,
                    '%CA_DIR_NAME_SHORT_OF%' => $model->req_dir_name_short_of,
                    '%AMOUNT_INT%' => Yii::$app->formatter->asDecimal($model->amount),
                    '%AMOUNT_INT_SPELL%' => Edf::spellNumberToRussian(intval($model->amount)),
                    '%AMOUNT_FRACT_INT%' => sprintf("%02.0f", ($model->amount - intval($model->amount)) * 100),
                ];

                if (($model->type_id == DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ || in_array($model->ct_id, [
                    ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                    ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                    ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА_БТ,
                    ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА_БТ,
                    ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                    ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ,
                    ContractTypes::CONTRACT_TYPE_НА_ОДНУ_СДЕЛКУ,
                ])) && (false !== $template->multi_stripos($template->name, ['Приложение 1', 'Приложение 4', 'Приложение 2']))) {
                    // табличная часть
                    // только для определенных типов договоров и шаблонов
                    $tp = $model->tablePart;
                    $iterator = 1;
                    foreach ($tp as $row) {
                        /* @var $row EdfTp */
                        $array_subst['%TP_FKKO_NAME_' . $iterator . '%'] = $row->fkko_name;
                        // изменено с кода ФККО на класс опасности по просьбе заказчика
                        //$array_subst['%TP_FKKO_' . $iterator . '%'] = !empty($row->fkko) ? $row->fkko->fkko_code : '';
                        $array_subst['%TP_FKKO_' . $iterator . '%'] = !empty($row->dc_id) ? str_replace(' класс', '', $row->dc->name) : '';
                        $array_subst['%TP_UNIT_' . $iterator . '%'] = $row->unitName;
                        $array_subst['%TP_HK_' . $iterator . '%'] = $row->hkName;
                        $array_subst['%TP_Q_' . $iterator . '%'] = Yii::$app->formatter->asDecimal($row->measure, 3);
                        $array_subst['%TP_PRICE_' . $iterator . '%'] = Yii::$app->formatter->asDecimal($row->price, 2);
                        $array_subst['%TP_AMOUNT_' . $iterator . '%'] = Yii::$app->formatter->asDecimal($row->amount, 2);

                        $iterator++;
                    }

                    // очистим от переменных оставшиеся строки шаблона, всего их там 70
                    // часть уже заполнена, остальные очищаем
                    if (count($tp) <= DocumentsController::TEMPLATE_APP_MAX_ROWS_COUNT) {
                        for ($i = $iterator; $i <= DocumentsController::TEMPLATE_APP_MAX_ROWS_COUNT; $i++) {
                            $array_subst['%TP_FKKO_NAME_' . $i . '%'] = '';
                            $array_subst['%TP_FKKO_' . $i . '%'] = '';
                            $array_subst['%TP_UNIT_' . $i . '%'] = '';
                            $array_subst['%TP_HK_' . $i . '%'] = '';
                            $array_subst['%TP_Q_' . $i . '%'] = '';
                            $array_subst['%TP_PRICE_' . $i . '%'] = '';
                            $array_subst['%TP_AMOUNT_' . $i . '%'] = '';
                        }
                    }
                }

                if ($model->ct_id == ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ) {
                    // для разовых договоров есть необходимость формировать табличную часть в виде одной строки
                    $wasteInline = '';
                    foreach ($model->tablePart as $row) {
                        if (!empty($row->fkko_id)) {
                            $wasteInline .= trim($row->fkko->fkko_name);
                        }
                        else {
                            $wasteInline .= trim($row->fkko_name);
                        }

                        if (!empty($row->measure)) {
                            $wasteInline .= ' (' . $row->measure . ' ' . $row->unitName . ')';
                        }
                        $wasteInline .= '; ';
                    }

                    if (!empty($wasteInline)) {
                        $wasteInline = trim($wasteInline, '; ');
                        $wasteInline .= ',';
                    }

                    $array_subst['%WASTE_INLINE%'] = $wasteInline;
                    unset($wasteInline);
                }

                $outputFilename = mb_strtolower(Yii::$app->security->generateRandomString() . '.docx', 'utf-8');
                $ffp = $model->files_full_path . '/' . $outputFilename;

                // если папка не существует, создадим ее
                if (!is_dir($model->files_full_path)) {
                    FileHelper::createDirectory($model->files_full_path);
                }

                $docx_gen = new \DocXGen;
                $docx_gen->docxTemplate($template->ffp, $array_subst, $ffp);

                if (preg_match('/^[a-z0-9]+\.[a-z0-9]+$/i', $ffp) !== 0 || !is_file("$ffp")) {
                    throw new NotFoundHttpException('Запрошенный файл не существует.');
                }

                (new EdfFiles([
                    'ed_id' => $model->id,
                    'ffp' => $ffp,
                    'fn' => $outputFilename,
                    'ofn' => $template->name . '.docx',
                    'size' => filesize($ffp),
                ]))->save();
            }
        }
        else {
            $dt_id = intval(Yii::$app->request->get('dt_id')); // тип документа
            $ct_id = intval(Yii::$app->request->get('ct_id')); // разновидность договора, если такой тип документа

            return $this->renderAjax('_templates', ['dataProvider' => $this->fetchTemplates($dt_id, $ct_id)]);
        }
    }

    /**
     * fill-fkko-tr-basis
     * @param $ca_id integer идентификатор контрагента, запроса на транспорт которого будут отобраны в список для выбора
     * @return mixed
     */
    public function actionFillFkkoTrBasis()
    {
        $model = new EdfFillFkkoBasisForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $counter = intval(Yii::$app->request->get('counter'));
                $counter = $counter + 1;
                $result = '';
                $edf = new Edf();
                switch ($model->src) {
                    case 1:
                        // в качестве источника данных выбран запрос на транспорт
                        $waste = TransportRequestsWaste::find()->where(['tr_id' => $model->tr_id])->all();

                        break;
                    case 2:
                        // в качестве источника данных выбран запрос лицензий
                        $waste = LicensesRequestsFkko::find()->where(['lr_id' => $model->lr_id])->all();

                        break;
                }

                foreach ($waste as $fkko) {
                    /* @var $fkko TransportRequestsWaste|LicensesRequestsFkko */

                    $tp = new EdfTp([
                        'fkko_id' => $fkko->fkko_id,
                    ]);

                    switch ($model->src) {
                        case 1:
                            $tp->fkko_name = $fkko->fkko_name;
                            $tp->unit_id = $fkko->unit_id;
                            $tp->measure = $fkko->measure;

                            break;
                        case 2:
                            $tp->fkko_name = $fkko->fkko->fkko_name;

                            break;
                    }

                    $result .= $this->renderPartial('_row_fkko', [
                        'edf' => $edf,
                        'model' => $tp,
                        'counter' => $counter,
                    ]);

                    $counter++;
                }

                return $this->renderContent($result);
            }
        }
        else {
            $ca_id = Yii::$app->request->get('ca_id');
            return $this->renderAjax('_fill_fkko_form', [
                'model' => $model,
                'tr' => ArrayHelper::map(TransportRequests::find()->select([
                    'id',
                    'rep' => 'CONCAT("№ ", `id`, " от ", FROM_UNIXTIME(`created_at`, \'%d.%m.%Y\'), " г.")',
                ])->where(['customer_id' => $ca_id])->orderBy('`created_at` DESC')->asArray()->all(), 'id', 'rep'),
                'lr' => ArrayHelper::map(LicensesRequests::find()->select([
                    'id',
                    'rep' => 'CONCAT("№ ", `id`, " от ", FROM_UNIXTIME(`created_at`, \'%d.%m.%Y\'), " г.")',
                ])->where(['ca_id' => $ca_id])->orderBy('`created_at` DESC')->asArray()->all(), 'id', 'rep'),
            ]);
        }
    }

    /**
     * Рендерит форму закрытия цикла жизни электронного документа, помещение отмеченных файлов в хранилище.
     * @return mixed
     */
    public function actionFinishEdf()
    {
        $model = new FinishEdfForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                foreach ($model->files as $file) {
                    $edfFileModel = EdfFiles::findOne($file);
                    if ($edfFileModel && false !== mb_stripos($edfFileModel->ffp, FileStorage::ROOT_FOLDER)) {
                        // делаем запрос вручную, чтобы не выполнялись дополительные действия
                        /*
                        Yii::$app->db->createCommand()->batchInsert(FileStorage::tableName(), [
                            'uploaded_at' => 'Дата и время загрузки',
                            'uploaded_by' => 'Автор загрузки',
                            'ca_id' => 'Контрагент',
                            'ca_name' => 'Контрагент',
                            'type_id' => 'Тип контента',
                            'ffp' => 'Полный путь к файлу',
                            'fn' => 'Имя файла',
                            'ofn' => 'Оригинальное имя файла',
                            'size' => 'Размер файла'
                        ], []);
                        */

                        $modelStorage = new FileStorage();
                        $modelStorage->attributes = $edfFileModel->attributes;
                        if (!empty($edfFileModel->ed)) {
                            switch ($edfFileModel->ed->type_id) {
                                case DocumentsTypes::TYPE_ДОГОВОР:
                                    $modelStorage->type_id = UploadingFilesMeanings::ТИП_КОНТЕНТА_ДОГОВОР;
                                    break;
                                case DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ:
                                    $modelStorage->type_id = UploadingFilesMeanings::ТИП_КОНТЕНТА_ДОПСОГЛАШЕНИЕ;
                                    break;
                            }
                            $modelStorage->ca_id = $edfFileModel->ed->fo_ca_id;
                            $modelStorage->ca_name = $edfFileModel->ed->req_name_short;
                        }
                        $modelStorage->file = true; // просто, чтобы пропустило

                        $modelStorage->save();
                    }
                }
                $edf = $model->edf;
                $edf->state_id = EdfStates::STATE_ЗАВЕРШЕНО;
                $edf->is_received_original = true;
                return $edf->save();
            }
        }
        else {
            $model->edf_id = intval(Yii::$app->request->get('id'));
            return $this->renderAjax('_finish_edf_form', ['model' => $model]);
        }

        return false;
    }

    /**
     * Отправляет реквизиты из формы электронного документа в CRM Fresh Office.
     */
    public function actionPushToFreshOffice()
    {
        if (null !== Yii::$app->request->post('id')) {
            $ed = $this->findModel(intval(Yii::$app->request->post('id')));
            if (foCompanyDetails::find()
                ->where([
                    'ID_COMPANY' => $ed->fo_ca_id,
                    'INN' => $ed->req_inn,
                    'KPP' => $ed->req_kpp,
                    'OGRN_CLIENT' => $ed->req_ogrn,
                    'ADRES_YUR' => $ed->req_address_j,
                    'ADRES_FACT' => $ed->req_address_f,
                    'KR_NAME' => $ed->req_name_short,
                    'FULL_NAME' => $ed->req_name_full,
                    'RS' => $ed->req_an,
                    'KS' => $ed->req_ca,
                    'BIK' => $ed->req_bik,
                ])
                ->count() == 0
            ) {
                return (new foCompanyDetails([
                    'ID_COMPANY' => $ed->fo_ca_id,
                    'INN' => $ed->req_inn,
                    'KPP' => $ed->req_kpp,
                    'OGRN_CLIENT' => $ed->req_ogrn,
                    'ADRES_YUR' => $ed->req_address_j,
                    'ADRES_FACT' => $ed->req_address_f,
                    'KR_NAME' => $ed->req_name_short,
                    'FULL_NAME' => $ed->req_name_full,
                    'RS' => $ed->req_an,
                    'KS' => $ed->req_ca,
                    'BIK' => $ed->req_bik,
                    'BANK_NAME' => $ed->req_bn,
                    'DIR_NAME' => $ed->req_dir_name,
                    'DIR_NAME_1' => $ed->req_dir_name_of,
                    'DIR_NAME_4' => $ed->req_dir_name_short,
                    'DIR_STATUS' => $ed->req_dir_post,
                ]))->save();
            }
            else return true;
        }

        return false;
    }

    /**
     * Отправляет товары в CRM Fresh Office как товары контрагента.
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionPullGoodsToFreshOffice()
    {
        if (Yii::$app->request->isPost && null !== Yii::$app->request->post('id')) {
            $ed = Edf::findOne(intval(Yii::$app->request->post('id')));
            if (null !== Yii::$app->request->post('addons')) {
                $tpIds = Yii::$app->request->post('addons');
            }
            else {
                $tpIds = [];
            }

            if ($ed) {
                // электронный документ идентифицирован
                // выполним перебор табличной части, создадим новые товары, если его нет еще
                foreach ($ed->tablePart as $fkko) {
                    // в следующих переменных будут храниться значения условий отбора, они же будут использоваться для
                    // создания нового элемента, если потребуется его создавать
                    $goodName = '';
                    $dcName = '';
                    $hkName = '';
                    $unitId = null;

                    $query = foGoods::find();

                    // определим наименование товара, по которому будем осуществлять поиск
                    if (!empty($fkko->fkko_id)) {
                        $goodName = trim($fkko->fkko->fkko_name);
                    }
                    elseif (!empty(trim($fkko->fkko_name))) {
                        $goodName = trim($fkko->fkko_name);
                    }

                    // при необходимости дополним наименование стандартной для услуг фразой
                    if (in_array($fkko->id, $tpIds)) {
                        $goodName = 'Оказание услуг по обращению с отходом: "' . $goodName . '"';
                    }

                    if (!empty(($goodName))) {
                        // наименование определено, уточняем текст запроса
                        $query->where([
                            'DISCRIPTION_TOVAT' => $goodName,
                        ]);
                    }

                    // уточним запрос значением класса опасности, если удастся его определить
                    if (!empty($fkko->fkko)) {
                        $dcName = DangerClasses::dangerClassRome(substr(trim($fkko->fkko->fkko_code), -1));
                    }
                    elseif (!empty($fkko->dc_id)) {
                        $dcName = DangerClasses::dangerClassRome(substr(trim($fkko->dc_id), -1));
                    }
                    if (!empty($dcName)) {
                        $query->andWhere([
                            'ADD_klass_opasnosti' => $dcName,
                        ]);
                    }

                    // уточним текст запроса видом обращения, если он задан
                    if (!empty($fkko->hk)) {
                        $hkName = trim($fkko->hk->name);
                        $query->andWhere([
                            'ADD_sposob' => $hkName,
                        ]);
                    }

                    // уточним текст запроса значением единицы измерения, если она задана
                    if (!empty($fkko->unit_id)) {
                        $foUnit = foUnits::findOne(['UNITCode' => $fkko->unit->code]);
                        if ($foUnit) {
                            $unitId = $foUnit->UNITID;
                            $query->andWhere([
                                'UNITID' => $unitId,
                            ]);
                        }
                    }

                    $success = false;
                    $model = $query->one();
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model === null) {
                            if (!empty($goodName)) {
                                $model = new foGoods();
                                $model->DISCRIPTION_TOVAT = $goodName;
                                if (!empty($dcName)) $model->ADD_klass_opasnosti = $dcName;
                                if (!empty($hkName)) $model->ADD_sposob = $hkName;
                                if (!empty($unitId)) $model->UNITID = $unitId;
                                $success = $model->save();
                            }
                        }
                        else {
                            $success = true;
                        }

                        if ($success) {
                            $properties = [
                                'ID_TOVAR' => $model->ID_TOVAR,
                                'ID_COMPANY' => $ed->fo_ca_id,
                            ];
                            $modelCompanyGood = foCompanyGoods::find()->where($properties)->one();

                            if ($modelCompanyGood === null) {
                                $modelCompanyGood = new foCompanyGoods(ArrayHelper::merge($properties, [
                                    'PRICE' => $fkko->price,
                                ]));
                            }
                            else {
                                $modelCompanyGood->PRICE = $fkko->price;
                            }

                            if ($modelCompanyGood->save()) {
                                $transaction->commit();
                                continue;
                            }
                        }

                        $transaction->rollBack();
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    } catch (\Throwable $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Отображает список диалогов документа, идентификатор которого передается в параметрах.
     * @param $id integer идентификатор документа
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDialogMessagesList($id)
    {
        $newMessage = new EdfDialogs();
        $newMessage->ed_id = $id;
        $newMessage->created_by = Yii::$app->user->id;

        return $this->render('_dialogs', [
            'dataProvider' => $this->fetchDialogs($this->findModel($id)),
            'model' => $newMessage,
            'action' => self::ADD_DIALOG_MESSAGE_URL,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionAddDialogMessage()
    {
        $model = new EdfDialogs();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $newMessage = new EdfDialogs();
            $newMessage->ed_id = $model->ed->id;
            $newMessage->created_by = Yii::$app->user->id;

            return $this->render('_dialogs', [
                'dataProvider' => $this->fetchDialogs($model->ed),
                'model' => $newMessage,
                'action' => self::ADD_DIALOG_MESSAGE_URL,
            ]);
        }
    }

    /**
     * Выполняет подбор файлов из хранилища, применяется отбор по контрагенту.
     * list-of-storage-files-typeahead
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionListOfStorageFilesTypeahead()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $q = Yii::$app->request->get('q');
            $ca_id = Yii::$app->request->get('ca_id');

            $query = FileStorage::find()->select([
                'id',
                'value' => 'ofn',
            ])->where([
                'ca_id' => $ca_id,
            ])->andFilterWhere(['like', 'ofn', $q]);

            return $query->asArray()->all();
        }
    }

    /**
     * Выполняет добавление файла из файлового хранилища в документ (просто копируется запись в базе данных).
     * add-file-from-storage
     */
    public function actionAddFileFromStorage()
    {
        if (Yii::$app->request->post('ed_id') !== null) {
            $ed_id = intval(Yii::$app->request->post('ed_id'));
        }
        if (Yii::$app->request->post('file_id') !== null) {
            $file_id = intval(Yii::$app->request->post('file_id'));
        }

        if (!empty($ed_id) && !empty($file_id)) {
            $file = FileStorage::findOne($file_id);
            if ($file) {
                return (new EdfFiles([
                    'ed_id' => $ed_id,
                    'ffp' => $file->ffp,
                    'fn' => $file->fn,
                    'ofn' => $file->ofn,
                    'size' => $file->size,
                ]))->save();
            }
        }
    }

    /**
     * Помещает выделенные пользователем файлы в архив и отдает его на скачивание.
     * download-selected-files
     * @return mixed
     */
    public function actionDownloadSelectedFiles()
    {
        if (Yii::$app->request->get('id') !== null && Yii::$app->request->get('ids') !== null) {
            $ids = explode(',', Yii::$app->request->get('ids'));
            $model = Edf::findOne(intval(Yii::$app->request->get('id')));
            if ($model) {
                // электронный документ обнаружен, можно работать
                $files = EdfFiles::find()->where(['id' => $ids])->all();
                if (count($files) > 0) {
                    $tempFn = Yii::$app->security->generateRandomString(8) . '.zip';
                    $tempFfp = Yii::getAlias('@uploads') . '/' . $tempFn;

                    $zip = new \ZipArchive();
                    $zip->open($tempFfp, \ZipArchive::CREATE);

                    foreach ($files as $file) {
                        // проверить бы, что файл действительно существует на диске
                        if (file_exists($file->ffp)) {
                            // файл существует, копируем его во временный файл, в котором будут производиться изменения
                            $zip->addFile($file->ffp, $file->ofn);
                        }
                    }

                    $zip->close();
                    \Yii::$app->response->sendFile($tempFfp, 'edf_' . $model->id . '_extraction-' . $tempFn, ['mimeType' => 'application/zip']);
                    if (file_exists($tempFfp)) unlink($tempFfp);
                }
            }
        }
    }
}
