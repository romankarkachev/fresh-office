<?php

namespace backend\controllers;

use Yii;
use common\models\Tenders;
use common\models\TendersSearch;
use common\models\TendersTp;
use common\models\TendersTpSearch;
use common\models\TendersLogs;
use common\models\TendersLogsSearch;
use common\models\TendersFiles;
use common\models\TendersFilesSearch;
use common\models\LicensesRequests;
use common\models\LicensesRequestsFkko;
use common\models\LicensesRequestsStates;
use common\models\TendersWe;
use common\models\TendersStates;
use common\models\TendersContentTypes;
use common\models\TendersResults;
use common\models\TendersLossReasons;
use common\models\TendersLr;
use common\models\foCompany;
use common\models\foCompanyDetails;
use common\models\TenderFormsKinds;
use common\models\TenderFormsVarietiesKinds;
use common\models\TenderFormsKindsFieldsSearch;
use common\models\TenderParticipantForms;
use yii\base\Model;
use yii\db\StaleObjectException;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TendersController implements the CRUD actions for Tenders model.
 */
class TendersController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'tenders';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Тендеры';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования тендера
     */
    const UPDATE_URL = 'update';
    const UPDATE_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::UPDATE_URL];

    /**
     * URL для добавления новой строки в табличную часть "Отходы" (только при создании тендера)
     */
    const URL_RENDER_WASTE_ROW = 'render-waste-row';
    const URL_RENDER_WASTE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_WASTE_ROW];

    /**
     * URL для интерактивного добавления новой строки табличной части "Отходы" тендера
     */
    const URL_CREATE_WASTE = 'create-waste';
    const URL_CREATE_WASTE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE_WASTE];

    /**
     * URL для интерактивного удаления строки табличной части "Отходы"
     */
    const URL_DELETE_WASTE = 'delete-waste';
    const URL_DELETE_WASTE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_WASTE];

    /**
     * URL для дополнения табличной части "Отходы" строками из запроса лицензий
     */
    const URL_FILL_FKKO = 'fill-fkko';
    const URL_FILL_FKKO_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_FILL_FKKO];

    /**
     * URL для загрузки файлов через ajax
     */
    const URL_UPLOAD_FILES = 'upload-files';
    const URL_UPLOAD_FILES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPLOAD_FILES];

    /**
     * URL для скачивания приаттаченных файлов
     */
    const URL_DOWNLOAD_FILE = 'download-file';
    const URL_DOWNLOAD_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_FILE];

    /**
     * URL для предварительного просмотра файлов через ajax
     */
    const URL_PREVIEW_FILE = 'preview-file';
    const URL_PREVIEW_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PREVIEW_FILE];

    /**
     * URL для удаления файлов через ajax
     */
    const URL_DELETE_FILE = 'delete-file';
    const URL_DELETE_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_FILE];

    /**
     * URL для рендеринга поля с возможностью выбора запроса лицензий
     */
    const URL_LR_CASTING = 'lr-casting';
    const URL_LR_CASTING_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_LR_CASTING];

    /**
     * URL для поиска тендера по его номеру
     */
    const FIND_TENDER_BY_NUMBER_URL = 'find-tender-by-number';
    const FIND_TENDER_BY_NUMBER_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::FIND_TENDER_BY_NUMBER_URL];

    /**
     * URL для интерактивного изменения исполнителя по тендеру
     */
    const URL_TAKE_WORK_OVER = 'take-over';
    const URL_TAKE_WORK_OVER_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_TAKE_WORK_OVER];

    /**
     * URL для интерактивного обновления списка файлов
     */
    const URL_RENDER_FILES_LIST = 'render-files-list';
    const URL_RENDER_FILES_LIST_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_FILES_LIST];

    /**
     * URL для интерактивного обновления журнала событий
     */
    const URL_RENDER_LOGS_LIST = 'render-logs-list';
    const URL_RENDER_LOGS_LIST_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_LOGS_LIST];

    /**
     * URL для скачивания выделенных пользователем файлов одним архивом
     */
    const URL_DOWNLOAD_SELECTED_FILES = 'download-selected-files';
    const URL_DOWNLOAD_SELECTED_FILES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_SELECTED_FILES];

    /**
     * URL рендерит поле с причиной проигрыша (отказа)
     */
    const URL_RENDER_FIELD_REASON = 'render-field-reason';
    const URL_RENDER_FIELD_REASON_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_FIELD_REASON];

    /**
     * URL повторной первичной идентификации
     */
    const URL_PRIMARY_RECOGNITION = 'primary-recognition';
    const URL_PRIMARY_RECOGNITION_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PRIMARY_RECOGNITION];

    /**
     * URL для интерактивного сохранения введенных вручную данных о победителе
     */
    const URL_SUBMIT_TENDER_RESULTS = 'submit-tender-results';

    /**
     * URL для переноса причин проигрыша из текста комментария в специально созданную для этого таблицу базы данных
     * Одноразовый модуль.
     */
    const URL_PROCESS_LOSS_REASONS = 'process-loss-reasons';

    /**
     * URL рендерит формы с их полями выбранной разновидности
     */
    const URL_RENDER_FORMS = 'render-forms';
    const URL_RENDER_FORMS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_FORMS];

    /**
     * URL делает замену переменных в шаблоне, шаблон помещает в zip-архив и отдает на скачивание пользователю
     */
    const URL_GENERATE_TENDER_FORMS = 'generate-tender-forms';
    const URL_GENERATE_TENDER_FORMS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_GENERATE_TENDER_FORMS];

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
                        'actions' => [self::URL_DOWNLOAD_FILE, 'temp'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', self::UPDATE_URL,
                            self::URL_RENDER_WASTE_ROW, self::URL_CREATE_WASTE, self::URL_DELETE_WASTE, self::URL_FILL_FKKO,
                            self::URL_UPLOAD_FILES, self::URL_PREVIEW_FILE, self::URL_DELETE_FILE,
                            self::URL_LR_CASTING, self::FIND_TENDER_BY_NUMBER_URL, self::URL_TAKE_WORK_OVER,
                            self::URL_RENDER_FILES_LIST, self::URL_RENDER_LOGS_LIST, self::URL_DOWNLOAD_SELECTED_FILES,
                            self::URL_RENDER_FIELD_REASON, self::URL_PRIMARY_RECOGNITION, self::URL_PROCESS_LOSS_REASONS,
                            self::URL_SUBMIT_TENDER_RESULTS,
                            self::URL_RENDER_FORMS, self::URL_GENERATE_TENDER_FORMS,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'tenders_manager', 'sales_department_head', 'sales_department_manager', 'ecologist_head'],
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
                    self::URL_GENERATE_TENDER_FORMS => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку отходов, выбранных пользователями системы к тендеру.
     * @param $tender_id integer
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchWaste($tender_id)
    {
        $searchModel = new TendersTpSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['tender_id' => $tender_id]]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель строки табличной части "Отходы" для включения в тендер.
     * @param integer $tender_id тендер, в который добавляется строка табличной части "Отходы"
     * @return TendersTp
     */
    private function createNewWasteModel($tender_id = null)
    {
        return new TendersTp([
            'tender_id' => $tender_id,
        ]);
    }

    /**
     * Рендерит список отходов, включенных в тендер.
     * @param integer $tender_id идентификатор тендера
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderWaste($tender_id)
    {
        return $this->renderAjax('_waste_list', [
            'dataProvider' => $this->fetchWaste($tender_id),
            'model' => $this->createNewWasteModel($tender_id),
        ]);
    }

    /**
     * Делает выборку файлов, приаттаченных пользователями системы к тендеру.
     * @param $params integer|array
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchFiles($params)
    {
        $searchModel = new TendersFilesSearch();
        if (is_numeric($params)) {
            $params = [$searchModel->formName() => ['tender_id' => $params]];
        }
        if (empty($searchModel->ct_id)) $searchModel->ct_id = -1;

        $dataProvider = $searchModel->search($params);
        $dataProvider->setSort([
            'defaultOrder' => ['revision' => SORT_DESC, 'uploaded_at' => SORT_DESC],
        ]);
        $dataProvider->pagination = false;

        return [
            $searchModel,
            $dataProvider
        ];
    }

    /**
     * Рендерит список файлов, прикрепленных к тендеру.
     * @param integer $tender_id идентификатор тендера
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderFiles($tender_id)
    {
        list($searchFilesModel, $dataProvider) = $this->fetchFiles($tender_id);
        return $this->renderAjax('_files_list', [
            'searchModel' => $searchFilesModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Делает выборку записей из журнала событий.
     * @param $params integer|array
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchLogs($params)
    {
        $searchModel = new TendersLogsSearch();
        if (is_numeric($params)) {
            $params = [$searchModel->formName() => ['tender_id' => $params]];
        }
        if (empty($searchModel->type)) $searchModel->type = 1;

        $dataProvider = $searchModel->search($params);

        return [
            $searchModel,
            $dataProvider,
        ];
    }

    /**
     * Делает выборку победителя в торгах.
     * @param $params integer|array
     * @return TendersResults[]
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchWinner($params)
    {
        return TendersResults::find()->where($params)->all();
    }

    /**
     * Lists all Tenders models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TendersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Tenders model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new Tenders();
        $modelsEquipment = [];
        $modelsWaste = [];

        if ($model->load(Yii::$app->request->post())) {
            $success = true;

            // загрузим модели используемого оборудования к тендеру
            if (!empty(Yii::$app->request->post($model->formName())['we'])) {
                foreach (Yii::$app->request->post($model->formName())['we'] as $i => $data) {
                    $modelsEquipment[$i] = new TendersWe([
                        'we_id' => $data,
                    ]);
                }
            }

            // загрузим модели отходов к тендеру
            if (isset(Yii::$app->request->post($model->formName())['crudeWaste'])) {
                foreach (Yii::$app->request->post($model->formName())['crudeWaste'] as $i => $data) {
                    $newModel = new TendersTp();
                    $newModel->load($data, '');
                    $modelsWaste[$i] = $newModel;
                    unset($newModel);
                }
            }

            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

            try {
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelsEquipment) && $valid;
                $valid = Model::validateMultiple($modelsWaste) && $valid;
                if ($valid) {
                    // основная модель успешно прошла валидацию, запишем ее
                    $model->save(false);

                    // создаем оборудование к тендеру
                    foreach ($modelsEquipment as $newModel) {
                        // не менять на updateAttributes() потому что модель еще не записана
                        $newModel->tender_id = $model->id;
                        $newModel->validate(null, false) && $newModel->save(false) ? null : $success = false;
                    }

                    // создаем отходы к тендеру
                    foreach ($modelsWaste as $newModel) {
                        // не менять на updateAttributes() потому что модель еще не записана
                        $newModel->tender_id = $model->id;
                        $newModel->validate(null, false) && $newModel->save(false) ? null : $success = false;
                    }

                    if ($success) $transaction->commit(); else $transaction->rollBack();

                    return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::UPDATE_URL, 'id' => $model->id]);
                } else {
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw new BadRequestHttpException($e->getMessage(), 0, $e);
            }

            $model->crudeWaste = $modelsWaste;
        }
        else {
            $model->findTool = Tenders::FIND_TENDER_TOOL_ID;

            // текущий менеджер должен быть задан изначально, если такова его роль
            if (Yii::$app->user->can('sales_department_manager')) {
                $model->manager_id = Yii::$app->user->id;
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Tenders model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $state = $model->state_id;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $success = true;

                // если нажата кнопка "Отправить на согласование"
                if (Yii::$app->request->post('approve_request') !== null) $model->state_id = TendersStates::STATE_СОГЛАСОВАНИЕ_РОП;

                // если нажата кнопка "Согласовать"
                elseif (Yii::$app->request->post('approve') !== null) {
                    if ($model->state_id == TendersStates::STATE_СОГЛАСОВАНИЕ_РУКОВОДСТВОМ) {
                        $model->state_id = TendersStates::STATE_СОГЛАСОВАНА;
                    }
                    else {
                        $model->state_id = TendersStates::STATE_СОГЛАСОВАНИЕ_РУКОВОДСТВОМ;
                    }
                }

                // если нажата кнопка "Отказать"
                elseif (Yii::$app->request->post('reject') !== null) $model->state_id = TendersStates::STATE_ОТКАЗ;

                // если нажата кнопка "Отозвать"
                elseif (Yii::$app->request->post('revoke') !== null) $model->state_id = TendersStates::STATE_ЧЕРНОВИК;

                // если нажата кнопка "Взять в работу"
                elseif (Yii::$app->request->post('take_over') !== null) {
                    if (empty($model->responsible_id)) {
                        $model->responsible_id = Yii::$app->user->id;
                        $model->state_id = TendersStates::STATE_В_РАБОТЕ;
                    }
                    else {
                        $success = false;
                        $model->addError('comment', 'Закупка уже находится в разработке у другого исполнителя.');
                    }
                }

                // если нажата кнопка "Заявка подана"
                elseif (Yii::$app->request->post('submitted') !== null) $model->state_id = TendersStates::STATE_ЗАЯВКА_ПОДАНА;

                // если нажата кнопка "Дозапрос"
                elseif (Yii::$app->request->post('refinement') !== null) $model->state_id = TendersStates::STATE_ДОЗАПРОС;

                // если нажата кнопка "Победа"
                elseif (Yii::$app->request->post('victory') !== null) $model->state_id = TendersStates::STATE_ПОБЕДА;

                // если нажата кнопка "Проигрыш"
                elseif (Yii::$app->request->post('defeat') !== null) $model->state_id = TendersStates::STATE_ПРОИГРЫШ;

                // если нажата кнопка "Без результатов"
                elseif (Yii::$app->request->post('abyss') !== null) $model->state_id = TendersStates::STATE_БЕЗ_РЕЗУЛЬТАТОВ;

                // если нажата кнопка "Отправить на согласование"
                if (Yii::$app->request->post('approve_request') !== null) $model->state_id = TendersStates::STATE_СОГЛАСОВАНИЕ_РОП;

                // если нажата кнопка "Отменен заказчиком"
                if (Yii::$app->request->post('withdrew') !== null) $model->state_id = TendersStates::STATE_ОТМЕНЕН_ЗАКАЗЧИКОМ;

                // если нажата кнопка "Опоздание"
                if (Yii::$app->request->post('late') !== null) $model->state_id = TendersStates::STATE_ОПОЗДАНИЕ;

                if ($success) {
                    // если не провалилось где-то на предыдущих этапах
                    $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

                    if ($model->save()) {
                        $modelsEquipment = [];

                        // загрузим модели используемого оборудования к тендеру
                        if (!empty(Yii::$app->request->post($model->formName())['we'])) {
                            foreach (Yii::$app->request->post($model->formName())['we'] as $i => $data) {
                                $modelsEquipment[$i] = new TendersWe([
                                    'we_id' => $data,
                                ]);
                            }
                        }

                        // удаляем имеющиеся записи об используемом оборудовании
                        TendersWe::deleteAll();

                        try {
                            // создаем оборудование к тендеру
                            foreach ($modelsEquipment as $newModel) {
                                $newModel->tender_id = $model->id;
                                $newModel->validate(null, false) && $newModel->save(false) ? null : $success = false;
                            }
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                            throw new BadRequestHttpException($e->getMessage(), 0, $e);
                        }
                    }
                    else {
                        $success = false;
                    }

                    if ($success) {
                        $transaction->commit();
                        return $this->redirect(self::ROOT_URL_AS_ARRAY);
                    }
                    else {
                        $model->state_id = $state; // возвращаем статус
                        $transaction->rollBack();
                    }
                }
            }
            else {
                // возвращаем статус
                $model->state_id = $state;
            }
        }
        else {
            // обновление наименования контрагента каждый раз, когда открывается заявка
            // обновление происходит только тогда, когда наименование действительно изменилось
            $foCompany = foCompany::findOne($model->fo_ca_id);
            if ($foCompany) {
                if (mb_strtolower(trim($model->fo_ca_name)) != mb_strtolower(trim($foCompany->COMPANY_NAME))) {
                    $model->updateAttributes([
                        'fo_ca_name' => trim($foCompany->COMPANY_NAME),
                    ]);
                }
            }

            // используемое оборудование
            $model->we = TendersWe::find()->select('we_id')->where(['tender_id' => $model->id])->column();
        }

        list($searchFilesModel, $dpFiles) = $this->fetchFiles($id);
        list($searchLogsModel, $dpLogs) = $this->fetchLogs($id);

        return $this->render('update', [
            'model' => $model,
            'newWasteModel' => $this->createNewWasteModel($id),
            'searchFilesModel' => $searchFilesModel,
            'searchLogsModel' => $searchLogsModel,
            'dpWaste' => $this->fetchWaste($id),
            'dpFiles' => $dpFiles,
            'dpLogs' => $dpLogs,
        ]);
    }

    /**
     * Deletes an existing Tenders model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $this->findModel($id)->delete();
        } catch (StaleObjectException $e) {
        } catch (NotFoundHttpException $e) {
        } catch (\Throwable $e) {
        }

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the Tenders model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tenders the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tenders::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Рендерит новую строку табличной части "Отходы".
     * render-waste-row
     * @param $counter integer
     * @return string
     */
    public function actionRenderWasteRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->createNewWasteModel();

            return $this->renderAjax('_row_waste_fields', [
                'model' => $model,
                'parentModel' => new Tenders(),
                'form' => new \yii\bootstrap\ActiveForm(),
                'counter' => (intval($counter) + 1),
            ]);
        }

        return false;
    }

    /**
     * Выполняет интерактивное добавление строки табличной части тендера.
     * create-waste
     * @return mixed
     * @throws \Throwable
     */
    public function actionCreateWaste()
    {
        if (Yii::$app->request->isPjax) {
            $model = new TendersTp();

            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if ($model->save()) {
                        $transaction->commit();
                        return $this->renderWaste($model->tender_id);
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

        return false;
    }

    /**
     * Выполняет интерактивное удаление строки табличной части из тендера.
     * delete-waste
     * @param integer $id идентификатор тендера
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteWaste($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = TendersTp::findOne($id);
            if ($model) {
                $tender_id = $model->tender_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderWaste($tender_id);
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

        return false;
    }

    /**
     * Дополняет табличную часть тендера строками из запроса лицензий.
     * fill-fkko
     * @param $counter integer номер строки в форме тендера
     * @param $lr_id integer идентификатор запроса лицензий
     * @return mixed
     */
    public function actionFillFkko($counter, $lr_id)
    {
        $counter = intval($counter);
        $result = '';
        $tender = new Tenders();
        $waste = LicensesRequestsFkko::find()->where(['lr_id' => $lr_id])->all();
        foreach ($waste as $fkko) {
            /* @var $fkko LicensesRequestsFkko */

            $counter++;

            $tp = new TendersTp([
                'fkko_id' => $fkko->fkko_id,
                'fkko_name' => $fkko->fkkoRep,
            ]);

            $result .= $this->renderAjax('_row_waste_fields', [
                'parentModel' => $tender,
                'model' => $tp,
                'form' => new \yii\bootstrap\ActiveForm(),
                'counter' => $counter,
            ]);
        }

        return $result;
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
        $tender = Tenders::findOne($obj_id);
        if ($tender) {
            $upload_path = TendersFiles::getUploadsFilepath($tender);
            if ($upload_path === false) return 'Невозможно создать папку для хранения загруженных файлов!';

            // массив загружаемых файлов
            $files = $_FILES['files'];
            // массив имен загружаемых файлов
            $filenames = $files['name'];
            if (count($filenames) > 0) {
                for ($i=0; $i < count($filenames); $i++) {
                    // идиотское действие, но без него
                    // PHP Strict Warning: Only variables should be passed by reference
                    $tmp = explode('.', basename($filenames[$i]));
                    $ext = end($tmp);
                    $filename = mb_strtolower(Yii::$app->security->generateRandomString() . '.'.$ext, 'utf-8');
                    $filepath = $upload_path . '/' . $filename;
                    if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                        $fu = new TendersFiles([
                            'tender_id' => $obj_id,
                            'ffp' => $filepath,
                            'fn' => $filename,
                            'ofn' => $filenames[$i],
                            'size' => filesize($filepath),
                            'ct_id' => TendersContentTypes::CONTENT_TYPE_ПОЛЬЗОВАТЕЛЬСКИЕ,
                        ]);
                        if ($fu->validate()) $fu->save(); else return 'Загруженные данные неверны.';
                    }
                }
            }
        }

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
            $model = TendersFiles::findOne($id);
            if (file_exists($model->ffp))
                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param integer $id
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteFile($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = TendersFiles::findOne($id);
            if ($model) {
                $tender_id = $model->tender_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderFiles($tender_id);
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
    }

    /**
     * Выполняет предварительный показ изображения.
     * @param $id
     * @return mixed
     */
    public function actionPreviewFile($id)
    {
        $model = TendersFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage()) {
                $tender = $model->tender;
                $createdAt = $tender->created_at;
                $placedAt = $tender->placed_at;
                $filepath = Yii::getAlias('@uploads-tenders-fs') . '/' . date('Y', $placedAt) . '/' . date('m', $placedAt) . '/' . date('d', $placedAt) . '/' . $tender->id . '/' . $model->ffp;
                if (file_exists($filepath)) {
                    $subPath = date('Y', $placedAt) . '/' . date('m', $placedAt) . '/' . date('d', $placedAt) . '/' . $model->tender_id;
                }
                else {
                    $subPath = date('Y', $createdAt) . '/' . date('m', $createdAt) . '/' . date('d', $createdAt) . '/' . $model->tender_id;
                }
                $filepath = Yii::getAlias('@uploads-tenders') . '/' . $subPath . '/' . $model->fn;

                return \yii\helpers\Html::img($filepath, ['width' => '100%']);
            }
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(ArrayHelper::merge(self::URL_DOWNLOAD_FILE_AS_ARRAY, ['id' => $id])) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Рендерит поле с выбором запроса лицензий.
     * @param $org_id integer организация
     * @param $ca_id integer контрагент для отбора запросов лицензий
     * @return mixed
     */
    public function actionLrCasting($org_id, $ca_id)
    {
        $tableName = LicensesRequests::tableName();
        $lrsTableName = LicensesRequestsStates::tableName();
        $licenceRequestsFiltered = LicensesRequests::find()->select([
            'id' => $tableName . '.id',
            'name' => 'CONCAT("№ ", `' . $tableName . '`.`id`, " от ", FROM_UNIXTIME(`' . $tableName . '`.`created_at`, \'%d.%m.%Y\'), " г. (", ' . $lrsTableName . '.`name`, ", ", `profile`.`name`, ", отходов: ", (' . LicensesRequestsFkko::find()->select(['waste' => 'COUNT(*)'])->where('`lr_id` = `' . $tableName . '`.`id`')->createCommand()->getRawSql() . '), ")")',
        ])
            ->joinWith(['state', 'createdByProfile'])
            ->where(['org_id' => $org_id, 'ca_id' => $ca_id])->orderBy('`created_at` DESC')->asArray()->all();

        return $this->renderAjax('_field_lr', [
            'model' => new Tenders(),
            'form' => new \yii\bootstrap\ActiveForm(),
            'licenceRequestsFiltered' => ArrayHelper::map($licenceRequestsFiltered, 'id', 'name'),
        ]);
    }

    /**
     * Выполняет парсинг страницы со страницей извещения о закупке.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionFindTenderByNumber()
    {
        $model = new Tenders();
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            switch ($model->findTool) {
                case Tenders::FIND_TENDER_TOOL_ID:
                    // для начала определим, нам передана ссылка или сразу номер закупки
                    $mode = '';
                    if (stripos($model->urlSource, 'zakupki.gov')) {
                        if (stripos($model->urlSource, '/223/')) {
                            // ссылка на закупку по закону 223
                            $mode = Tenders::TENDERS_223_PAGE_COMMON;
                        }
                        elseif (stripos($model->urlSource, '/ea44/')) {
                            // ссылка на закупку по закону 44
                            $mode = Tenders::TENDERS_44_PAGE_COMMON;
                        }
                        elseif (stripos($model->urlSource, '/ok504/')) {
                            // ссылка на закупку по закону 504
                            $mode = Tenders::TENDERS_504_PAGE_COMMON;
                        }
                    }
                    else {
                        if (strlen($model->urlSource) == Tenders::REG_NUMBER_LENGTH_223) {
                            $mode = Tenders::TENDERS_223_PAGE_COMMON;
                            $model->oos_number = $model->urlSource;
                        }
                        elseif (strlen($model->urlSource) == Tenders::REG_NUMBER_LENGTH_44) {
                            $mode = Tenders::TENDERS_44_PAGE_COMMON;
                            $model->oos_number = $model->urlSource;
                        }
                    }

                    if (!empty($mode)) {
                        if (preg_match("/.*\?regNumber=(\d+)$/", $model->urlSource, $output_array)){
                            $model->oos_number = $output_array[1];
                            unset($output_array);
                        }

                        return $model->fetchTenderByNumber($mode);
                    }
                    else {
                        return false;
                    }
                    break;
                case Tenders::FIND_TENDER_TOOL_REQS:
                    break;
            }
        }
    }

    /**
     * Выполняет интерактивное изменение исполнителя по тендеру.
     * take-over
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionTakeOver()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = intval(Yii::$app->request->post('id'));
        if ($id > 0) {
            // добавить условие! только тендерист может взять на себя
            // Yii::$app->user->can('tenders_manager') || Yii::$app->user->can('root')
            $model = $this->findModel($id);
            if (!empty($model->responsible_id)) {
                return [
                    'result' => false,
                    'errorMsg' => 'Тендер уже находится в работе. Невозможно изменить исполнителя!',
                ];
            }

            // если здесь не сработало 404, то выполняем интерактивное изменение:
            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
            try {
                if ($model->updateAttributes([
                    'responsible_id' => Yii::$app->user->id,
                    'state_id' => TendersStates::STATE_В_РАБОТЕ,
                ]) > 0) {
                    // делаем запись об этом в историю
                    if ((new TendersLogs([
                        'tender_id' => $id,
                        'description' => 'Тендер собственноручно взят в работу.',
                    ]))->save()) {
                        $transaction->commit();
                        return [
                            'result' => true,
                            'responsibleName' => Yii::$app->user->identity->profile->name,
                        ];
                    }
                    else {
                        $transaction->rollBack();
                    }
                }
            }
            catch (\Exception $e) {
                $transaction->rollBack();
                throw new BadRequestHttpException($e->getMessage(), 0, $e);
            }
        }

        return ['result' => false];
    }

    /**
     * Рендерит один только список файлов.
     * render-files-list
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRenderFilesList()
    {
        list($searchModel, $dataProvider) = $this->fetchFiles(Yii::$app->request->queryParams);

        return $this->renderPartial('_files_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Рендерит один только список логов.
     * render-logs-list
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRenderLogsList()
    {
        list($searchModel, $dataProvider) = $this->fetchLogs(Yii::$app->request->queryParams);

        return $this->renderAjax('_logs_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Помещает выделенные пользователем файлы в архив и отдает его на скачивание.
     * download-selected-files
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionDownloadSelectedFiles()
    {
        if (Yii::$app->request->get('id') !== null && Yii::$app->request->get('ids') !== null) {
            $ids = explode(',', Yii::$app->request->get('ids'));
            $model = Tenders::findOne(intval(Yii::$app->request->get('id')));
            if ($model) {
                // тендер обнаружен, можно работать
                $files = TendersFiles::find()->where(['id' => $ids])->all();
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
                    \Yii::$app->response->sendFile($tempFfp, 'tender_' . $model->oos_number . '-' . $tempFn, ['mimeType' => 'application/zip']);
                    if (file_exists($tempFfp)) unlink($tempFfp);
                }
            }
        }
    }

    /**
     * Рендерит поле с причиной проигрыша или отказа.
     * @param integer $mode 1 - проигрыш, 2 - отказ
     * @return mixed
     */
    public function actionRenderFieldReason($mode = 1)
    {
        $model = new Tenders();

        switch ($mode) {
            case 1:
                return $this->renderAjax('_field_loss_reason', ['model' => $model]);
            case 2:
                return $this->renderAjax('_field_reject_reason', ['model' => $model]);
        }
    }

    /**
     * Модуль первичной идентификации. Для тех случаев, когда тендер создавался без номера закупки и соответственно изменения
     * в нем не отслеживаются. Вернуть к жизни можно вставив номер и вызвав эту функцию.
     * primary-recognition
     * @param $id integer тендер
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPrimaryRecognition($id)
    {
        $model = $this->findModel($id);
        $model->primaryRecognition();
    }

    /**
     * Интерактивное сохранение данных о победителе, введенных вручную.
     * submit-tender-results
     */
    public function actionSubmitTenderResults()
    {
        if (Yii::$app->request->isPost) {
            $model = new TendersResults();
            if ($model->load(Yii::$app->request->post())) {
                // попробуем идентифицировать контрагента как сущность Fresh Office
                try {
                    $foCompany = foCompanyDetails::findOne(['INN' => $model->inn, 'KPP' => $model->kpp]);
                    if (!empty($foCompany)) {
                        // есть такой контрагент, сохраняем его ID
                        $model->fo_ca_id = $foCompany->ID_COMPANY;
                    }
                }
                catch (\Exception $e) { }

                if ($model->save()) {
                    return $this->renderAjax('_winner', ['model' => $model]);
                }
                else {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return false;
                }
            }
        }
    }

    /**
     * Выполняет перенос причин проигрыша в специально созданную для этого таблицу.
     * process-loss-reasons
     */
    public function actionProcessLossReasons()
    {
        // возможные причины проигрышей
        $lossReasons = TendersLossReasons::arrayMapForSelect2();

        // тендеры, которые успешно проиграны
        $tenders = Tenders::find()->where(['state_id' => TendersStates::STATE_ПРОИГРЫШ])->andWhere(['like', 'comment', 'причина проигрыша'])->all();
        $tendersCount = count($tenders);

        // причины, уже вынесенные в отдельную таблицу, для исключения дублирования при многократном запуске модуля
        $existingLrs = TendersLr::find()->select('tender_id')->column();

        $successCount = 0;

        print '<p>Начало обработки. Выбрано тендеров: ' . $tendersCount . '</p>';
        foreach ($tenders as $tender) {
            if (!ArrayHelper::isIn($tender->id, $existingLrs)) {
                foreach ($lossReasons as $id => $name) {
                    print '<p>' . $tender->id . '. Поиск причины ' . $name . '...</p>';
                    if (false !== mb_stripos($tender->comment, 'причина проигрыша: ' . $name)) {
                        (new TendersLr([
                            'tender_id' => $tender->id,
                            'lr_id' => $id,
                        ]))->save() ? $successCount++ : null;
                        print '<p>Идентифицирована причина проигрыша тендера ' . $tender->id . ': ' . $id . ' (' . $name . ').</p>';
                        break;
                    }
                }
            }
            else {
                $successCount++;
            }
        }

        $success = ' Все записи успешно обработны.';
        if ($tendersCount != $successCount) {
            $success = ' Успешно обработано тендеров: ' . $successCount . ' из ' . $tendersCount . '.';
        }

        print '<p>Обработка завершена.' . $success . '</p>';
    }

    /**
     * Рендерит формы выбранной разновидности с их полями.
     * render-forms
     * @param $id int идентификатор разновидности, формы которой запрашиваются
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRenderForms($id)
    {
        $searchModel = new TenderFormsKindsFieldsSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'searchByVariety' => intval($id),
            ],
        ]);

        return $this->renderAjax('_participant_form', ['dataProvider' => $dataProvider]);
    }

    /**
     * generate-tender-forms
     * @throws \yii\base\Exception
     */
    public function actionGenerateTenderForms()
    {
        $templateBaseDir = TenderFormsVarietiesKinds::getUploadsFilepath();
        $model = new TenderParticipantForms();
        if ($model->load(Yii::$app->request->post())) {
            $kinds = TenderFormsKinds::arrayMapForSelect2(); // формы
            $subStruct = []; // массив переменных для замены
            $filesToArchive = []; // массив с файлами, которые необходимо будет поместить в архив
            $docxGen = new \DocXGen;

            foreach ($model->items as $index => $form) {
                $kindName = '';
                if (isset($kinds[$index])) {
                    $kindName = $kinds[$index];
                }

                foreach ($form as $fieldAlias => $fieldValue) {
                    if ($fieldAlias == 'fieldName') {
                        // это поле содержит наименование формы, его не нужно включать в массив для замены
                        $fieldName = $fieldValue;
                        continue;
                    }

                    $fieldAlias = '%' . $fieldAlias . '%';
                    if (!empty($fieldValue) && !ArrayHelper::isIn($fieldAlias, $subStruct)) {
                        // если поле пользователем заполнено, то берем такое значение и помещаем в массив переменных для замены
                        $subStruct[$fieldAlias] = $fieldValue;
                    }
                }
                unset($fieldAlias);

                if (count($subStruct) > 0) {
                    $templateDir = $templateBaseDir . '/' . $model->variety_id;
                    $templateFfp = $templateDir . '/' . $index . '.docx';
                    if (!empty($kindName)) {
                        $fn = $kindName . '.docx';
                    }
                    else {
                        $fn = 'test-' . md5($model->variety_id . '-' . $index) . '.docx';
                    }

                    $ffp = $templateDir . '/' . $fn;
                    if (file_exists($templateFfp)) {
                        $docxGen->docxTemplate($templateFfp, $subStruct, $ffp);

                        if (preg_match('/^[a-z0-9]+\.[a-z0-9]+$/i', $fn) !== 0 || !is_file("$ffp")) {
                            throw new NotFoundHttpException('Запрошенный файл не существует.');
                        }

                        $filesToArchive[] = [
                            'ffp' => $ffp,
                            'fn' => $fn,
                        ];
                    }
                    unset($templateDir, $templateFfp, $fn, $ffp);
                }
            }

            if (count($filesToArchive) > 0) {
                $tempFn = Yii::$app->security->generateRandomString(8) . '.zip';
                $tempFfp = Yii::getAlias('@uploads') . '/' . $tempFn;

                $zip = new \ZipArchive();
                $zip->open($tempFfp, \ZipArchive::CREATE);

                foreach ($filesToArchive as $file) {
                    // файл существует, копируем его во временный файл, в котором будут производиться изменения
                    $zip->addFile($file['ffp'], $file['fn']);
                }

                $zip->close();
                \Yii::$app->response->sendFile($tempFfp, 'Формы-' . $tempFn, ['mimeType' => 'application/zip']);
                if (file_exists($tempFfp)) unlink($tempFfp);
                foreach ($filesToArchive as $file) {
                    if (file_exists($file['ffp'])) unlink($file['ffp']);
                }

            }
        }
    }

    public function actionTemp()
    {
        //$tender = Tenders::find()->orderBy(['created_at' => SORT_DESC])->one(); // просто последний берем
        $tender = Tenders::findOne(152);
        if ($tender) {
            $tender->oos_number = 32009096213;
            // только, если он существует, конечно же
            $lawNo = (int)$tender->law_no;
            $lawNo++;

            $logs = $tender->fetchTenderByNumber(Tenders::TENDERS_223_PAGE_PROTOCOLS);
            var_dump($logs);

            /*
            $files = $tender->fetchTenderByNumber($lawNo);
            //$files = $tender->fetchTenderByNumber(Tenders::TENDERS_223_PAGE_FILES);
            $new = $tender->obtainAuctionFiles($files, true);
            var_dump($files, $new);
            */
        }
    }
}
