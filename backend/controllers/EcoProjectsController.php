<?php

namespace backend\controllers;

use Yii;
use common\models\EcoProjects;
use common\models\EcoProjectsSearch;
use common\models\EcoTypesMilestones;
use common\models\EcoMilestones;
use common\models\EcoProjectsMilestones;
use common\models\EcoProjectsMilestonesFiles;
use common\models\EcoProjectsAccess;
use common\models\EcoProjectsAccessSearch;
use common\models\EcoProjectsMilestonesFilesSearch;
use common\models\EcoProjectsFiles;
use common\models\EcoProjectsFilesSearch;
use common\models\EcoProjectsLogs;
use common\models\PoEp;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EcoProjectsController implements the CRUD actions for EcoProjects model.
 */
class EcoProjectsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'eco-projects';

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const MILESTONE_FILES_URL_FOR_SORT_PAGING = 'eco-projects';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Проекты по экологии';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_MILESTONES_SORT_PAGING = 'eco-projects/update';

    /**
     * URL для обработки формы добавления нового этапа
     */
    const URL_ADD_USER_ACCESS = 'add-user-access';
    const URL_ADD_USER_ACCESS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_ADD_USER_ACCESS];

    /**
     * URL для удаления этапа проекта
     */
    const URL_DELETE_USER_ACCESS = 'delete-user-access';
    const URL_DELETE_USER_ACCESS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_USER_ACCESS];

    /**
     * URL для обработки формы добавления нового этапа
     */
    const URL_ADD_MILESTONE = 'add-project-milestone';
    const URL_ADD_MILESTONE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_ADD_MILESTONE];

    /**
     * URL для удаления этапа проекта
     */
    const URL_DELETE_MILESTONE = 'delete-project-milestone';
    const URL_DELETE_MILESTONE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_MILESTONE];

    /**
     * URL для удаления файла из этапа проекта
     */
    const URL_DOWNLOAD_MILESTONE_FILE = 'download-project-milestone-file';
    const URL_DOWNLOAD_MILESTONE_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_MILESTONE_FILE];

    /**
     * URL для удаления файла из этапа проекта
     */
    const URL_DELETE_MILESTONE_FILE = 'delete-project-milestone-file';
    const URL_DELETE_MILESTONE_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_MILESTONE_FILE];

    /**
     * URL для предварительного просмотра файлов этапов через ajax
     */
    const URL_PREVIEW_MILESTONE_FILE = 'preview-project-milestone-file';
    const URL_PREVIEW_MILESTONE_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PREVIEW_MILESTONE_FILE];

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
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['download-project-milestone-file-from-outside', 'download-file-from-outside'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update',
                            'user-access-list', self::URL_ADD_USER_ACCESS, self::URL_DELETE_USER_ACCESS,
                            self::URL_ADD_MILESTONE, self::URL_DELETE_MILESTONE, self::URL_DELETE_MILESTONE_FILE, self::URL_DOWNLOAD_MILESTONE_FILE, self::URL_PREVIEW_MILESTONE_FILE,
                            self::URL_UPLOAD_FILES, self::URL_DELETE_FILE, self::URL_DOWNLOAD_FILE, self::URL_PREVIEW_FILE,
                            'render-close-date-block', 'change-close-date',
                            'render-milestones-block', 'modal-projects-milestones', 'close-milestone',
                            'milestone-upload-files', 'preview-file',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'ecologist_head', 'ecologist', 'sales_department_head', 'sales_department_manager'],
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
                    self::URL_DELETE_USER_ACCESS => ['POST'],
                    self::URL_DELETE_MILESTONE_FILE => ['POST'],
                    'change-close-date' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку пользователей, имеющих доступ к проекту, переданному в параметрах.
     * @param $project_id integer of EcoProjects
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchUsersAccess($project_id)
    {
        $searchModel = new EcoProjectsAccessSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'project_id' => $project_id,
            ]
        ]);
        $dataProvider->pagination = false;
        $dataProvider->sort = false;

        return $dataProvider;
    }

    /**
     * Делает выборку файлов, приаттаченных пользователями системы к проекту.
     * @param $params array
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchFiles($params)
    {
        $searchModel = new EcoProjectsFilesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => $params]);
        $dataProvider->pagination = false;
        $dataProvider->sort = false;

        return $dataProvider;
    }

    /**
     * Рендерит список файлов, прикрепленных к проекту.
     * @param integer $project_id идентификатор проекта
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderFiles($project_id)
    {
        return $this->renderAjax('_files', [
            'dataProvider' => $this->fetchFiles(['project_id' => $project_id]),
        ]);
    }

    /**
     * Возвращает новую модель этапов проектов с заданным типом.
     * @param $project_id integer проект, для которого создается модель с доступом
     * @return EcoProjectsAccess
     */
    private function createNewAccessModel($project_id)
    {
        return new EcoProjectsAccess([
            'project_id' => $project_id,
        ]);
    }

    /**
     * Lists all EcoProjects models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EcoProjectsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = !empty(Yii::$app->request->get($searchModel->formName()));
        $progressFilterApplied = isset(Yii::$app->request->queryParams[$searchModel->formName()]['searchProgress']) && $searchModel->searchProgress != EcoProjectsSearch::FILTER_PROGRESS_ALL;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
            'progressFilterApplied' => $progressFilterApplied,
            'searchProgresses' => EcoProjectsSearch::fetchFilterProgresses(),
        ]);
    }

    /**
     * Creates a new EcoProjects model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new EcoProjects();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if (Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head') || Yii::$app->user->can('sales_department_head'))
                    return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->id]);
                else
                    return $this->redirect(self::ROOT_URL_AS_ARRAY);
            }
        } else {
            $model->date_start = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing EcoProjects model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // проверим наличие доступа у текущего пользователя к объекту
        // если, конечно, это не пользователь с полными правами или начальник отдела экологии
        if (!Yii::$app->user->can('root') && !Yii::$app->user->can('ecologist_head')) {
            $hasCurrentProjectAccess = $model->hasCurrentProjectAccess;
            $restricted = true;
            if (Yii::$app->user->can('sales_department_head')) {
                // начальник отдела продаж может просматривать только свои собственные проекты, проекты, в которые ему
                // открыт доступ, а также проекты, созданные менеджерами отдела продаж
                if ($model->createdByRoleName == 'sales_department_manager' || $model->created_by == Yii::$app->user->id || $hasCurrentProjectAccess) {
                    $restricted = false;
                }
            }
            else {
                $restricted = !$hasCurrentProjectAccess;
            }

            if ($restricted) {
                return $this->render('/common/forbidden_foreign', [
                    'details' => [
                        'breadcrumbs' => [['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY]],
                        'buttonCaption' => self::ROOT_LABEL,
                        'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                    ],
                ]);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->post('ahead_of_time') !== null) {
                $closedAt = time();
                // закроем все этапы проекта
                foreach (EcoProjectsMilestones::find()->where(['project_id' => $model->id, 'closed_at' => null])->all() as $milestone) {
                    $milestone->updateAttributes([
                        'closed_at' => $closedAt,
                    ]);
                }

                // закроем сам проект
                // последний закрытый этап закрыл бы и проект, если бы мы закрывали его через модель,
                // но для этапа может быть обязательным файл, а наличие файлов проверяется системой через базу,
                // что невозможно имитировать
                $model->updateAttributes([
                    'closed_at' => $closedAt,
                ]);

                Yii::$app->session->setFlash('success', 'Проект ' . $model->id . ' завершен досрочно.');
            }

            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'newAccessModel' => new EcoProjectsAccess(['project_id' => $id]),
            'dpAccess' => new ActiveDataProvider([
                'query' => EcoProjectsAccess::find()->where(['project_id' => $id]),
                'pagination' => false,
                'sort' => false,
            ]),
            'dpMilestones' => new ActiveDataProvider([
                'query' => EcoProjectsMilestones::find()
                    ->select([
                        '*',
                        'filesCount' => EcoProjectsMilestonesFiles::find()
                            ->select('COUNT(*)')
                            ->where(EcoProjectsMilestonesFiles::tableName() . '.project_milestone_id = ' . EcoProjectsMilestones::tableName() . '.id'),
                    ])
                    ->where(['project_id' => $id])
                    ->orderBy('order_no'),
                'pagination' => false,
                'sort' => false,
            ]),
            'dpPo' => new ActiveDataProvider([
                'query' => PoEp::find()
                    ->where(['ep_id' => $id])
                    ->joinWith(['po', 'ei'])
                    ->orderBy(['po.created_at' => SORT_DESC]),
                'pagination' => false,
                'sort' => false,
            ]),
            'dpLogs' => new ActiveDataProvider([
                'query' => EcoProjectsLogs::find()
                    ->where(['project_id' => $id])
                    ->joinWith(['createdByProfile', 'state'])
                    ->orderBy(['created_at' => SORT_DESC]),
                'pagination' => false,
                'sort' => false,
            ]),
            'dpFiles' => $this->fetchFiles(['project_id' => $id]),
        ]);
    }

    /**
     * Deletes an existing EcoProjects model.
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
     * Finds the EcoProjects model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return EcoProjects the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EcoProjects::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * @return mixed
     */
    public function actionRenderCloseDateBlock($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = EcoProjectsMilestones::findOne($id);
            if ($model) {
                return $this->renderAjax('_close_date_field', [
                    'model' => $model,
                    'form' => new \yii\bootstrap\ActiveForm(),
                ]);
            }
        }

        return false;
    }

    /**
     * Выполняет изменение планируемой даты этапа, идентификатор которого передан в параметрах. Также будет выполнен
     * пересчет следующих за этим этапом дат.
     * change-close-date
     * @param $id
     * @param $new_date
     * @return mixed
     */
    public function actionChangeCloseDate($id, $new_date)
    {
        $model = EcoProjectsMilestones::findOne($id);
        if ($model) {
            $model->date_close_plan = $new_date;
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Сроки этапа ' . $model->order_no . ' &laquo;<strong>' . $model->milestoneName .
                    '&raquo;</strong> изменены. В соответствии с изменениями был выполнен пересчет сроков следующих за текущим этапов.');
                return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->project_id]);
            }
        }
    }

    /**
     * Рендерит список этапов проекта по типу, переданному в параметрах
     * render-milestones-block
     * @param $type_id integer тип проекта
     * @param $date_start string дата начала работы над проектом
     * @return mixed
     */
    public function actionRenderMilestonesBlock($type_id, $date_start)
    {
        if (Yii::$app->request->isAjax) {
            $dateMilestoneClose = strtotime($date_start . ' 00:00:00');
            $dateProjectClose = $dateMilestoneClose;

            $milestones = EcoTypesMilestones::find()->select([
                EcoTypesMilestones::tableName() . '.id', 'milestoneName' => EcoMilestones::tableName() . '.name', 'is_file_reqiured', 'is_affects_to_cycle_time', 'time_to_complete_required', 'order_no',
            ])
                ->where(['type_id' => $type_id])
                ->orderBy('order_no')
                ->joinWith(['milestone'])
                ->asArray()->all();

            foreach ($milestones as $index => $milestone) {
                $calcDate = $milestone['time_to_complete_required'] * 24 *3600;
                if ($milestone['is_affects_to_cycle_time']) {
                    $dateProjectClose = $dateProjectClose + $calcDate;
                }

                $dateMilestoneClose += $calcDate;

                $milestones[$index]['date_close_plan'] = Yii::$app->formatter->asDate($dateMilestoneClose, 'php:d F Y г.');
            }

            $dateProjectClose = Yii::$app->formatter->asDate($dateProjectClose, 'php:Y-m-d');

            return $this->renderPartial('milestones_list_readonly', [
                'model' => new EcoProjects([
                    'date_close_plan' => $dateProjectClose,
                ]),
                'form' => new \yii\bootstrap\ActiveForm(),
                'dataProvider' => new ArrayDataProvider([
                    'modelClass' => 'common\models\EcoProjectsMilestones',
                    'allModels' => $milestones,
                    'key' => 'id', // поле, которое заменяет primary key
                    'pagination' => false,
                    'sort' => false,
                ])
            ]);
        }
    }

    /**
     * modal-projects-milestones
     * @param $id integer идентификатор проекта, для которого будут извлекаться фактически имеющиеся этапы
     * @return mixed
     */
    public function actionModalProjectsMilestones($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->findModel($id);
            return $this->renderPartial('milestones_list_modal', [
                'model' => $model,
                'dataProvider' => new ArrayDataProvider([
                    'modelClass' => 'common\models\EcoProjectsMilestones',
                    'allModels' => EcoProjectsMilestones::find()->where(['project_id' => $id])->orderBy('order_no')->all(),
                    'key' => 'id', // поле, которое заменяет primary key
                    'pagination' => false,
                    'sort' => false,
                ])
            ]);
        }
    }

    /**
     * Отображает список диалогов запроса, идентификатор которого передается в параметрах.
     * @param $id integer идентификатор запроса
     * @return mixed
     */
    public function actionUserAccessList($id)
    {
        return $this->render('access_list', [
            'dataProvider' => $this->fetchUsersAccess($id),
            'model' => $this->createNewAccessModel($id),
            'action' => self::URL_ADD_USER_ACCESS,
        ]);
    }

    /**
     * Выполняет интерактивное добавление пользователя в список имеющих доступ к проекту.
     * @return mixed
     */
    public function actionAddUserAccess()
    {
        if (Yii::$app->request->isPjax) {
            $model = new EcoProjectsAccess();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->renderAjax('access_list', [
                    'dataProvider' => $this->fetchUsersAccess($model->project_id),
                    'model' => $this->createNewAccessModel($model->project_id),
                    'action' => self::URL_ADD_USER_ACCESS,
                ]);
            }
        }

        return false;
    }

    /**
     * Выполняет удаление этапа из типа проектов.
     * @param $id integer идентификатор этапа проектов, который надо удалить
     * @return mixed
     */
    public function actionDeleteUserAccess($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = EcoProjectsAccess::findOne($id);
            if ($model) {
                $record_id = $model->project_id;
                $model->delete();

                return $this->renderAjax('access_list', [
                    'dataProvider' => $this->fetchUsersAccess($record_id),
                    'model' => $this->createNewAccessModel($record_id),
                    'action' => self::URL_ADD_USER_ACCESS,
                ]);
            }
        }

        return false;
    }

    /**
     * Выполняет интерактивное закрытие этапа. Возвращает массив с результатом выполнения и значениями для обновления,
     * если завершение этапа прошло успешно.
     * close-milestone
     * @param $id integer идентификатор этапа, который закрывается
     * @param $next_id integer
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws MethodNotAllowedHttpException если кто-то пытается выполнить интерактивный запрос вручную, втупую
     */
    public function actionCloseMilestone($id, $next_id=null)
    {
        $closedAt = time(); // дата и время закрытия этапа (а может быть и всего проекта, если этап последний)
        $model = EcoProjectsMilestones::findOne($id);

        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                // запрос отправлен интерактивно для закрытия этапа, не требующего файлов
                Yii::$app->response->format = Response::FORMAT_JSON;

                if ($model !== null) {
                    // этап идентифицирован
                    // проверим, не требуется ли предоставление файлов для закрытия данного этапа
                    if ($model->is_file_reqiured && $model->getEcoProjectsMilestonesFiles()->count() == 0) {
                        return [
                            'result' => false,
                            'errorMsg' => 'Для закрытия данного этапа необходимо загрузить в систему минимум один файл!',
                        ];
                    }

                    // проверим, не завершил ли кто-либо этот этап ранее
                    if (!empty($model->closed_at)) {
                        return [
                            'result' => false,
                            'errorMsg' => 'Этап уже завершен!',
                        ];
                    }

                    $toolNextColumnHtml = ''; // html-код для графы "Состояние" следующей строки таблицы (следующего этапа)
                    $isProjectClosed = false;
                    if (!empty($next_id)) {
                        $nextModel = EcoProjectsMilestones::findOne(intval($next_id));
                        if ($nextModel) {
                            // следующий этап обнаружен в базе, сформируем значение для колонки
                            $toolNextColumnHtml = EcoProjects::milestonesListToolColumn(null, $nextModel);
                        }
                    }
                    else {
                        // следующей строки нет, значит проект можно завершить
                        if ($model->project->ecoProjectsMilestonesPendingCount == 1) {
                            // остался только один незавершенный этап, завершим весь проект
                            if ($model->project->updateAttributes([
                                    'closed_at' => $closedAt,
                                ]) > 0) {
                                $isProjectClosed = true;
                            };
                        }
                    }

                    // дата, с которой начинать подсчет между предудыщим выполненным этапом и новым текущим
                    // если нет предыдущего выполненнго, то принимаем в качестве отправной точки дату запуска проекта в работу
                    $dateStart = strtotime($model->project->date_start . ' 00:00:00');
                    if (($lastMilestone = $model->project->lastMilestone) !== null) {
                        // есть завершенный этап, будем считать разницу во времени от него
                        $dateStart = $lastMilestone->closed_at;
                    }

                    return [
                        'result' => $model->updateAttributes([
                            'closed_at' => $closedAt,
                        ]) > 0,
                        'requiredColumnHtml' => EcoProjects::milestonesListTimeRequired($dateStart, $model),
                        'terminColumnHtml' => EcoProjects::milestonesListTerminColumn($model),
                        'filesColumnHtml' => EcoProjects::milestonesListFilesColumn(-1, $model, EcoProjectsMilestonesFiles::find()->where(EcoProjectsMilestonesFiles::tableName() . '.project_milestone_id = ' . $model->id)->count()),
                        'toolNextColumnHtml' => $toolNextColumnHtml,
                        'isProjectClosed' => $isProjectClosed,
                    ];
                } else {
                    throw new NotFoundHttpException('Запрошенная страница не существует.');
                }
            }
            else throw new MethodNotAllowedHttpException('Что это еще за фокусы?!');
        }
        else {
            if (Yii::$app->request->isPost) {
                if (Yii::$app->request->post('close_milestone') !== null && $model->load(Yii::$app->request->post())) {
                    // настало время завершить этап
                    $model->closed_at = $closedAt;
                    if ($model->validate()) {
                        if ($model->save()) {
                            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->project_id]);
                        }
                    }
                }
            }
            else {
                // проверим наличие доступа у текущего пользователя к объекту
                // если, конечно, это не пользователь с полными правами или начальник отдела экологии
                if (!Yii::$app->user->can('root') && !Yii::$app->user->can('ecologist_head') && !Yii::$app->user->can('sales_department_head')) {
                    if (!$model->project->hasCurrentProjectAccess) {
                        return $this->render('/common/forbidden_foreign', [
                            'details' => [
                                'breadcrumbs' => [['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY]],
                                'buttonCaption' => self::ROOT_LABEL,
                                'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                            ],
                        ]);
                    }
                }

                return $this->render('/eco-projects-milestones/update', [
                    'model' => $model,
                    'dpFiles' => ($searchModel = new EcoProjectsMilestonesFilesSearch())->search([
                        $searchModel->formName() => ['project_milestone_id' => $model->id],
                    ])
                ]);
            }
        }
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * milestone-upload-files
     * @return mixed
     */
    public function actionMilestoneUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = EcoProjectsMilestonesFiles::getUploadsFilepath();
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
                    $fu = new EcoProjectsMilestonesFiles();
                    $fu->project_milestone_id = $obj_id;
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
     * download-project-milestone-file-from-outside
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDownloadProjectMilestoneFileFromOutside($id)
    {
        $model = EcoProjectsMilestonesFiles::findOne(['id' => $id]);
        if (file_exists($model->ffp))
            return Yii::$app->response->sendFile($model->ffp, $model->ofn);
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Отдает на скачивание файл, на который позиционируется по идентификатору из параметров.
     * download-milestone-file
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDownloadProjectMilestoneFile($id)
    {
        if (is_numeric($id)) if ($id > 0) {
            $model = EcoProjectsMilestonesFiles::findOne($id);
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
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteProjectMilestoneFile($id)
    {
        $model = EcoProjectsMilestonesFiles::findOne($id);
        if ($model) {
            $record_id = $model->projectMilestone->id;
            $model->delete();


            return $this->redirect(['/' . self::MILESTONE_FILES_URL_FOR_SORT_PAGING . '/close-milestone', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Выполняет предварительный показ изображения.
     * @param $id integer идентификатор файла, который необходимо предварительно показать
     * @return mixed
     */
    public function actionPreviewProjectMilestoneFile($id)
    {
        $model = EcoProjectsMilestonesFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return Html::img(Yii::getAlias('@uploads-eco-projects') . '/' . $model->fn, ['width' => 600]);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/download-project-milestone-file-from-outside', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
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
        $uploadPath = EcoProjectsFiles::getUploadsFilepath($obj_id);
        if ($uploadPath === false) return 'Невозможно создать папку для хранения загруженных файлов!';

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
                $filepath = $uploadPath . '/' . $filename;
                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $fu = new EcoProjectsFiles([
                        'project_id' => $obj_id,
                        'ffp' => $filepath,
                        'fn' => $filename,
                        'ofn' => $filenames[$i],
                        'size' => filesize($filepath),
                    ]);
                    if ($fu->validate()) $fu->save(); else return 'Загруженные данные неверны.';
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
            $model = EcoProjectsFiles::findOne($id);
            if (file_exists($model->ffp))
                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Удаляет файл, приаттаченный к проекту по экологии.
     * @param integer $id
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteFile($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = EcoProjectsFiles::findOne($id);
            if ($model) {
                $project_id = $model->project_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderFiles($project_id);
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
     * Выполняет предварительный показ файла, приаттаченного к проекту по экологии.
     * @param $id
     * @return mixed
     */
    public function actionPreviewFile($id)
    {
        $model = EcoProjectsFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage()) {
                if (file_exists($model->ffp)) {
                    return \yii\helpers\Html::img(Yii::getAlias('@uploads-eco-projects') . '/' . $model->project_id . '/' . $model->fn, ['width' => '100%']);
                }
                else {
                    return 'Файл не найден.';
                }
            }
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/download-file-from-outside', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }
}
