<?php

namespace backend\controllers;

use Yii;
use common\models\Tasks;
use common\models\TasksSearch;
use common\models\TasksFiles;
use common\models\TasksFilesSearch;
use common\models\foCompany;
use common\models\foTasks;
use common\models\User;
use common\models\foManagers;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TasksController implements the CRUD actions for Tasks model.
 */
class TasksController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'tasks';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Задачи';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

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
     * URL для вывода модального окна с информацией по задаче
     */
    const URL_RENDER_TASK_SUMMARY = 'render-task-summary';
    const URL_RENDER_TASK_SUMMARY_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_TASK_SUMMARY];

    /**
     * URL для перезаполнения поля отбора "Исполнитель"
     */
    const URL_REFILL_RESPONSIBLE = 'refill-responsible';
    const URL_REFILL_RESPONSIBLE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_REFILL_RESPONSIBLE];

    /**
     * URL для вывода модального окна с информацией по задаче
     */
    const URL_TASK_POSTPONEMENT = 'task-postponement';
    const URL_TASK_POSTPONEMENT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_TASK_POSTPONEMENT];

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
                            'index', 'create', 'update', self::URL_UPLOAD_FILES, self::URL_PREVIEW_FILE,
                            self::URL_DELETE_FILE, self::URL_RENDER_TASK_SUMMARY, self::URL_REFILL_RESPONSIBLE,
                            self::URL_TASK_POSTPONEMENT,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_manager'],
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
                ],
            ],
        ];
    }

    /**
     * Lists all Tasks models.
     * @return mixed
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new TasksSearch();
        $searchModel->load($params);
        if (!isset($params['TasksSearch']['searchSource'])) {
            $searchModel->searchSource = TasksSearch::TASK_SOURCE_WEB_APP;
        }
        if ($searchModel->searchSource == TasksSearch::TASK_SOURCE_FO) {
            $dataProvider = $searchModel->searchFreshOffice($params);
        }
        else {
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Tasks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tasks();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Tasks model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
            }
        }
        else {
            // обновим информацию о контрагенте, если она изменилась
            $company = foCompany::findOne($model->fo_ca_id);
            if (trim($model->fo_ca_name) != trim($company->COMPANY_NAME)) {
                $model->updateAttributes([
                    'fo_ca_name' => trim($company->COMPANY_NAME),
                ]);
            }
        }

        // файлы к объекту
        $searchModel = new TasksFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['task_id' => $model->id]]);
        $dpFiles->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dpFiles->pagination = false;

        return $this->render('update', [
            'model' => $model,
            'dpFiles' => $dpFiles,
        ]);
    }

    /**
     * Deletes an existing Tasks model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the Tasks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tasks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tasks::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = TasksFiles::getUploadsFilepath();
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
                    $fu = new TasksFiles();
                    $fu->task_id = $obj_id;
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
            $model = TasksFiles::findOne($id);
            if (file_exists($model->ffp))
                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Выполняет предварительный показ изображения.
     * @param $id integer идентификатор файла, предварительный просмотр которого необходимо осуществить
     * @return mixed
     */
    public function actionPreviewFile($id)
    {
        $model = TasksFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return \yii\helpers\Html::img(Yii::getAlias('@uploads-tasks') . '/' . $model->fn, ['width' => '100%']);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_FILE, 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param $id
     * @return Response
     * @throws NotFoundHttpException если файл не будет обнаружен
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFile($id)
    {
        $model = TasksFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->task_id;
            $model->delete();

            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Рендерит в модальном окне сводную информацию по задаче.
     * @param $id integer идентификатор задачи, которая рендерится
     * @return mixed
     */
    public function actionRenderTaskSummary($id)
    {
        $model = foTasks::findOne($id);
        if ($model) {
            return $this->renderAjax('_summary', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $source_id integer источник выборки задач
     * @return mixed
     */
    public function actionRefillResponsible($source_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        switch ($source_id) {
            case TasksSearch::TASK_SOURCE_WEB_APP:
                return User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_ROLE);
            case TasksSearch::TASK_SOURCE_FO:
                return foManagers::arrayMapForSelect2();
        }
    }

    /**
     * Выполняет перенос задачи на другую дату, выбранную пользователем.
     * @param $id integer идентификатор задачи, которая переносится
     * @return mixed
     */
    public function actionTaskPostponement()
    {
        $model = new Tasks();
        if ($model->load(Yii::$app->request->post())) {
            $task = Tasks::findOne(Yii::$app->request->post('Tasks')['id']);
            echo $task->updateAttributes([
                'start_at' => $model->start_at,
            ]);
        }
        else {
            $model = Tasks::findOne(intval(Yii::$app->request->queryParams['id']));
            return $this->renderAjax('_postpone', ['model' => $model, 'form' => new \yii\bootstrap\ActiveForm()]);
        }
    }
}
