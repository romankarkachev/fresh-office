<?php

namespace backend\controllers;

use Yii;
use common\models\FileStorage;
use common\models\FileStorageSearch;
use common\models\FileStorageFolders;
use common\models\foProjects;
use common\models\UploadingFilesMeanings;
use common\models\DirectMSSQLQueries;
use common\models\FileStorageFilesEnumerator;
use common\models\FileStorageStats;
use common\models\FileStorageExtractionForm;
use common\models\StorageBulkImportForm;
use common\models\ProductionFeedbackFiles;
use common\models\StorageTtnRequired;
use moonland\phpexcel\Excel;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * StorageController implements the CRUD actions for FileStorage model.
 */
class StorageController extends Controller
{
    /**
     * Количество папок в каждой выборке
     */
    const FOLDERS_TO_DISPLAY = 5;

    /**
     * Наименования файлов и папок, которые будут пропущены при проходе
     */
    public $skip = ['.', '..', 'Thumbs.db', '1Документы на отходы', '1КП', '1ФОРМЫ ДЛЯ СРМ', '1С БИТ'];

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
                        'actions' => ['download-from-outside'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'scan-directory', 'store-enumerated-files', 'find-folder-by-name', 'casting-by-foldername',
                            'files-extraction',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'operator_head', 'logist'],
                    ],
                    [
                        'actions' => ['index', 'preview', 'download', 'bulk-import'],
                        'allow' => true,
                        'roles' => ['root', 'operator_head', 'sales_department_manager', 'sales_department_head', 'dpc_head', 'ecologist', 'ecologist_head', 'logist', 'accountant_b', 'tenders_manager'],
                    ],
                    [
                        'actions' => ['create', 'project-on-change'],
                        'allow' => true,
                        'roles' => ['root', 'operator_head', 'sales_department_manager', 'dpc_head', 'logist', 'accountant_b'],
                    ],
                    [
                        'actions' => ['update', 'delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'store-enumerated-files' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Проверяет, не обработан ли уже этот каталог.
     * @param $file string наименование папки контрагента
     * @return bool
     */
    protected function isFolderProcessed($file)
    {
        return FileStorageFilesEnumerator::find()->select('id')->where(['folder_name' => $file])->count() > 0;
    }

    /**
     * Определяет контент файла по его имени.
     * @param $ufm array массив с ключевыми словами и их соответствиями типам контента
     * @param $ffp string полный путь к файлу или только имя файла
     * @return integer|null
     */
    protected function determineContentMeaning($ufm, $ffp)
    {
        foreach ($ufm as $cm) {
            $keywords = explode(',', $cm['keywords']);
            if (count($keywords) > 0) {
                foreach ($keywords as $keyword) {
                    if ($keyword != null && false !== mb_stripos($ffp, $keyword)) return $cm['id'];
                }
            }
        }

        return null;
    }

    /**
     * Функция, которая извлекает только имя файла из пути для мультибайтовых кодировок.
     * Работает в винде и линуксе.
     * http://php.net/manual/ru/function.basename.php#121405
     * @param $path string исходный путь, из которого нужно излечь имя файла
     * @return string
     */
    protected function mb_basename($path) {
        if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Рекурсивным способом сканирует папку на наличие файлов в подпапках. Возвращает массив таких файлов.
     * @param $rootFolder string корневая папка, от которой идет рекурсия
     * @param $folder string текущая папка, которую нужно просмотреть
     * @param $ufm array массив типов контента для сопоставления
     * @return array
     */
    protected function scanFolderForFiles($rootFolder, $folder, $ufm)
    {
        $result = [];
        $files = scandir($folder);
        foreach ($files as $file) {
            if (!in_array($file, $this->skip)) {
                if (!is_file("$folder/$file")) {
                    $result = ArrayHelper::merge($result, $this->scanFolderForFiles($rootFolder, "$folder/$file", $ufm));
                }
                else {
                    $ffp = (str_replace($rootFolder, '', "$folder/$file"));
                    // попробуем определить тип контента по имени файла
                    $type_id = $this->determineContentMeaning($ufm, $this->mb_basename($ffp));
                    // если не получилось, то по всему пути к файлу
                    if(null === $type_id) $type_id = $this->determineContentMeaning($ufm, $ffp);

                    $result[] = [
                        'relativeFilePath' => $ffp,
                        'ffp' => $rootFolder . $ffp,
                        'fn' => $file,
                        'type_id' => $type_id,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Нумерует файлы по порядку.
     * @param $haystack array массив с файлами контрагент
     * @return mixed
     */
    protected function numerateFiles($haystack)
    {
        $result = $haystack;
        $iterator = 0;
        foreach ($result as $index => $file) {
            $result[$index]['number'] = $iterator;
            $iterator++;
        }

        return $result;
    }

    /**
     * Делает выборку первых папок контрагентов, проходится по их содержимому и формирует таким образом список файлов.
     */
    public function actionScanDirectory()
    {
        $rootFolder = FileStorage::ROOT_FOLDER;
        $manualTerminate = 0;
        $counter = 1;
        $result = [];

        $ufm = UploadingFilesMeanings::find()->select(['id', 'keywords'])->where('`keywords` IS NOT NULL')->asArray()->all();

        $files = scandir($rootFolder);
        foreach ($files as $file) {
            // папка не должна быть в списке исключений, не должна быть обработана ранее
            if (!in_array($file, $this->skip) && !is_file("$rootFolder/$file") && !$this->isFolderProcessed($file)) {
                // попытаемся контрагента идентифицировать
                // сначала попытаемся определить, не закреплена ли уже за ним какая-то папка
                $counteragent = FileStorageFolders::find()->select(['id', 'text' => 'fo_ca_name'])->where(['folder_name' => $file])->asArray()->one();
                if ($counteragent == null) {
                    // если не привязана, попытаемся определить контрагента по наименованию папки
                    $counteragent = DirectMSSQLQueries::fetchCounteragents($file);
                    if (count($counteragent) == 1) $counteragent = $counteragent[0];
                }

                // пронумеруем полученные файлы
                $files = $this->numerateFiles($this->scanFolderForFiles("$rootFolder/$file", "$rootFolder/$file", $ufm));

                $result[] = [
                    'folderName' => $file,
                    'caId' => isset($counteragent['id']) ? $counteragent['id'] : null,
                    'caName' => isset($counteragent['text']) ? $counteragent['text'] : null,
                    'files' => $files,
                    'counter' => $counter,
                ];

                $manualTerminate++;
                $counter++;
            }

            if ($manualTerminate == self::FOLDERS_TO_DISPLAY) break;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => false,
            ],
            'sort' => [
                'defaultOrder' => ['folderName' => SORT_ASC],
                'attributes' => [
                    'folderName',
                    'caId',
                    'caName',
                ],
            ],
        ]);

        return $this->render('enum', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Поступивший post-запрос разбирает на отдельные файлы и по ним делает запись в базу.
     */
    public function actionStoreEnumeratedFiles()
    {
        $files = Yii::$app->request->post('EnumFiles');

        $storedFolders = FileStorageFolders::find()->select('folder_name')->asArray()->column();
        $processedFolders = FileStorageFilesEnumerator::find()->select('folder_name')->asArray()->column();

        $iterator = 0;
        $errors = '';
        foreach ($files as $file) {
            // пропускаем пустые, где контрагент не указан
            if ($file['ca_id'] == null) {
                // помещаем такие папки в специальную таблицу с особенным признаком, чтобы вернуться к ним позднее
                if (!in_array($file['folder_name'], $processedFolders)) {
                    $model = new FileStorageFilesEnumerator([
                        'folder_name' => $file['folder_name'],
                        'type' => FileStorageFilesEnumerator::TYPE_ОТЛОЖЕНА,
                    ]);
                    $model->save();
                    unset($model);

                    $processedFolders[] = $file['folder_name'];
                }

                continue;
            };

            $model = new FileStorage([
                'ca_id' => $file['ca_id'],
                'ca_name' => $file['ca_name'],
                'type_id' => $file['type_id'],
                'ffp' => $file['ffp'],
                'fn' => $file['fn'],
                'ofn' => $file['fn'],
                'size' => filesize($file['ffp']),
            ]);
            if ($model->save(false)) {
                // добавляем папку в обработанные
                if (!in_array($file['folder_name'], $processedFolders)) {
                    $model = new FileStorageFilesEnumerator([
                        'folder_name' => $file['folder_name'],
                    ]);
                    $model->save();
                    unset($model);

                    $processedFolders[] = $file['folder_name'];
                }

                // закрепляем папку за этим контрагентом
                // это его папка и теперь туда будут сыпаться файлы, с ним связанные
                if (!in_array($file['folder_name'], $storedFolders)) {
                    $model = new FileStorageFolders([
                        'fo_ca_id' => $file['ca_id'],
                        'fo_ca_name' => $file['ca_name'],
                        'folder_name' => $file['folder_name'],
                    ]);
                    $model->save();
                    unset($model);

                    $storedFolders[] = $file['folder_name'];
                }

                $iterator++;
            }
            else {
                $err = '<p><strong>Папка ' . $file['folder_name'] . ', файл ' . $file['fn'] . ':</strong></p>';
                foreach ($model->errors as $error) $err .= $error[0] . '<br />';
                $errors .= $err;
            }
        }

        if ($iterator > 0) Yii::$app->session->setFlash('success', 'Успешно зафиксировано файлов: ' . $iterator . '.');

        if ($errors != '') {
            Yii::$app->session->setFlash('error', 'При выполнении операции возникли ошибки:' . $errors . '.');
        }

        $this->redirect(['/storage/scan-directory']);
    }

    /**
     * Lists all FileStorage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FileStorageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $foreignRecord = false;
        /*
        if (!Yii::$app->user->can('root')) {
            // если пользователь не обладает полными правами, то он может просматривать только своих контрагентов
            // проверим также, установлено ли у текущего пользователя соответствие пользователю во Fresh Office
            if ($searchModel->ca_id != null) {
                $ca = DirectMSSQLQueries::fetchCounteragent($searchModel->ca_id);
                if (count($ca) == 1 && $ca[0]['managerId'] != Yii::$app->user->identity->profile->fo_id) $foreignRecord = true;
            }
        }
        */

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'foreignRecord' => $foreignRecord,
        ]);
    }

    /**
     * Creates a new FileStorage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FileStorage();
        $model->scenario = 'create';

        $params = [
            'model' => $model,
        ];

        if ($model->load(Yii::$app->request->post())) {
            $pifp = $model->getUploadsFilepath();
            if ($pifp === false)
                $model->addError('ca_id', 'Не удалось создать папку в хранилище.');
            else {
                // проверим, закреплена ли эта папка за данным контрагентом
                $storedFolder = FileStorageFolders::findOne(['fo_ca_id' => $model->ca_id, 'folder_name' => $model->caFolderName]);
                if ($storedFolder == null) {
                    // папка еще не закреплена за этим контрагентом, закрепляем:
                    $storedFolder = new FileStorageFolders([
                        'fo_ca_id' => $model->ca_id,
                        'fo_ca_name' => $model->ca_name,
                        'folder_name' => $model->caFolderName,
                    ]);
                    $storedFolder->save();
                }

                $arrayOfModels = []; // в массив будут помещаться все успешно импортированные файлы, запись в базу произойдет пачкой и разом
                $uploadedAt = time();
                $uploadedBy = Yii::$app->user->id;
                $model->file = UploadedFile::getInstances($model, 'file');
                foreach ($model->file as $file) {
                    $fn = Yii::$app->security->generateRandomString() . '.' . $file->extension;
                    $ffp = $pifp . '/' . $fn;

                    if ($file->saveAs($ffp)) {
                        $arrayOfModels[] = [
                            // дата и время загрузки (одно для всех)
                            $uploadedAt,
                            // автор загрузки
                            $uploadedBy,
                            // контрагент
                            $model->ca_id,
                            // наименование контрагента
                            $model->ca_name,
                            // тип контента
                            $model->type_id,
                            // полный путь
                            $ffp,
                            // имя
                            $fn,
                            // оргиниальное имя
                            $file->name,
                            // размер
                            filesize($ffp),
                        ];
                    }
                }

                $rowsAffected = 0;
                if (count($arrayOfModels) > 0) {
                    $rowsAffected = Yii::$app->db->createCommand()->batchInsert(FileStorage::tableName(), [
                        'uploaded_at',
                        'uploaded_by',
                        'ca_id',
                        'ca_name',
                        'type_id',
                        'ffp',
                        'fn',
                        'ofn',
                        'size',
                    ], $arrayOfModels)->execute();
                }

                if ($rowsAffected > 0 ) {
                    // запоминаем контрагента и тип контента
                    Yii::$app->session->set('storage_ca_id_' . Yii::$app->user->id, $model->ca_id);
                    Yii::$app->session->set('storage_ca_name_' . Yii::$app->user->id, $model->ca_name);
                    Yii::$app->session->set('storage_type_id_' . Yii::$app->user->id, $model->type_id);

                    // изменим в проекте значение в поле "ТТН"
                    if (!empty($model->project_id) && $model->type_id == UploadingFilesMeanings::ТИП_КОНТЕНТА_ТТН) {
                        // снаружи пришел идентификатор проекта, это означает, что пользователь хочет изменить значение в поле "ТТН" проекта
                        $project = foProjects::findOne($model->project_id);
                        if ($project) {
                            $value = '';
                            switch ($model->is_scan) {
                                case 0:
                                    $value = 'да';
                                    break;
                                case 1:
                                    $value = 'скан';
                                    break;
                            }
                            if (!empty($value)) {
                                $project->updateAttributes(['ADD_ttn' => $value]);
                            }
                        }
                    }
                }

                return $this->redirect(['create']);
            }
        }
        else {
            $key = 'storage_ca_id_' . Yii::$app->user->id;
            $sessionCaId = Yii::$app->session->get($key);
            if ($sessionCaId != null) {
                $model->ca_id = $sessionCaId;
                $params['bcCa'] = ['label' => Yii::$app->session->get('storage_ca_name_' . Yii::$app->user->id), 'url' => ['/storage', 'FileStorageSearch' => ['ca_id' => $model->ca_id]]];

                Yii::$app->session->remove($key);
            }
            unset($key);

            $key = 'storage_type_id_' . Yii::$app->user->id;
            $sessionTypeId = Yii::$app->session->get($key);
            if ($sessionTypeId != null) {
                $model->type_id = $sessionTypeId;
                Yii::$app->session->remove($key);
            }
        }

        return $this->render('create', $params);
    }

    /**
     * Updates an existing FileStorage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post())) {
            $model->file = true; // помечаем, что файл предоставлен, чтобы успешно пройти валидацию

            if ($model->save()) return $this->redirect(['/storage']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Показывает контент файла.
     * @param integer $id
     * @return mixed
     */
    public function actionPreview($id)
    {
        $model = $this->findModel($id);

        // зафиксируем этот просмотр пользователем
        // если с момента последнего просмотра прошло определенное отведенное время
        $doStore = true;
        if (FileStorageStats::FREE_TIME_TO_PREVIEW_FILE > 0) {
            $stat = FileStorageStats::find()
                ->where('created_at > ' . (time() - FileStorageStats::FREE_TIME_TO_PREVIEW_FILE))
                ->andWhere([
                    'created_by' => Yii::$app->user->id,
                    'type' => FileStorageStats::STAT_TYPE_ПРОСМОТР,
                    'fs_id' => $id,
                ])
                ->all();
            if ($stat != null) $doStore = false;
        }

        if ($doStore) {
            $stat = new FileStorageStats([
                'created_by' => Yii::$app->user->id,
                'type' => FileStorageStats::STAT_TYPE_ПРОСМОТР,
                'fs_id' => $id,
            ]);
            $stat->save();
        }

        return $this->render('preview', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing FileStorage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        Url::remember(Yii::$app->request->referrer);
        $this->findModel($id)->delete();
        return $this->goBack();
    }

    /**
     * Finds the FileStorage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FileStorage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FileStorage::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Отдает на скачивание файл, на который позиционируется по идентификатору из параметров.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDownload($id)
    {
        $id = intval($id);
        if ($id > 0) {
            $model = FileStorage::findOne($id);
            if (file_exists($model->ffp)) {
                // зафиксируем это скачивание пользователем
                // если с момента последнего скачивания прошло определенное отведенное время
                $doStore = true;
                if (FileStorageStats::FREE_TIME_TO_DOWNLOAD_FILE > 0) {
                    $stat = FileStorageStats::find()->where([
                        'and',
                        'created_at > ' . (time() - FileStorageStats::FREE_TIME_TO_DOWNLOAD_FILE),
                        [
                            'type' => FileStorageStats::STAT_TYPE_СКАЧИВАНИЕ,
                            'fs_id' => $id,
                        ],
                    ])->all();

                    if ($stat != null) $doStore = false;
                }

                if ($doStore) {
                    $stat = new FileStorageStats([
                        'created_by' => Yii::$app->user->id,
                        'type' => FileStorageStats::STAT_TYPE_СКАЧИВАНИЕ,
                        'fs_id' => $id,
                    ]);
                    $stat->save();
                }

                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            }
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }

    /**
     * Отдает на скачивание файл, на который позиционируется по идентификатору из параметров.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDownloadFromOutside($id)
    {
        $model = FileStorage::findOne(['id' => $id]);
        if (file_exists($model->ffp))
            return Yii::$app->response->sendFile($model->ffp, $model->ofn);
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Производит поиск папки на диске по ее имени.
     * Используется для автоматического определения папки контрагента в хранилище файлов.
     * @param $id integer идентификатор контрагента
     * @param $name string наименование контрагента, по нему будет осуществлен поиск папки
     * @return mixed
     */
    public function actionFindFolderByName($id, $name)
    {
        $id = intval($id);
        if ($id > 0) {
            $folderExists = false;
            $folderName = '';
            $canChange = true;

            // папка контрагента уже может быть в базе
            $storedFolder = FileStorageFolders::find()->where(['fo_ca_id' => $id])->one();
            if ($storedFolder != null) {
                // у контрагента есть папка уже
                $folderExists = true;
                $folderName = $storedFolder->folder_name;
                $canChange = false;
            }
            else {
                // контрагент наверное еще не проходил парсинг, поэтому папка еще не назначена,
                // но она может существовать на диске. Вместе с Сергеем Дружко проверим это.
                $folderExists = file_exists(FileStorage::ROOT_FOLDER . '/' . $name);
                if ($folderExists) $folderName = $name; else $folderName = $id;
            }

            return $this->renderAjax('_fs_folder', [
                'model' => new FileStorage(),
                'form' => new ActiveForm(),
                'ca_id' => intval($id),
                'ca_name' => $name,
                'folderExists' => $folderExists,
                'folderName' => $folderName,
                'canChange' => $canChange,
            ]);
        }

        return false;
    }

    /**
     * Выполняет подбор папки по части наименования, переданного в параметрах.
     * @return array
     */
    public function actionCastingByFoldername($q)
    {
        $iterator = 1;
        $result = [];

        $dir = opendir(FileStorage::ROOT_FOLDER);

        while(($file = readdir($dir)) !== false) {
            if (mb_stripos($file, $q) !== false) {
                $result[] = [
                    'id' => $iterator,
                    'text' => $file,
                ];
            }

            $iterator++;
        }
        closedir($dir);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['results' => $result];
    }

    /**
     * Отображает форму, указав условия в которой возможно отобрать файлы контрагентов и скачать их одним архивом.
     * @return mixed
     */
    public function actionFilesExtraction()
    {
        $model = new FileStorageExtractionForm();
        if ($model->load(Yii::$app->request->post())) {
            $files = FileStorage::find()->where(['in', 'ca_id', $model->fo_ca_ids])->andWhere(['in', 'type_id', $model->type_ids])->orderBy('ca_id')->all();
            if (count($files) == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет файлов для помещения в архив.');
            }
            else {
                $filepath = FileStorageExtractionForm::getUploadsFilepath();
                if (false == $filepath) {
                    Yii::$app->getSession()->setFlash('error', 'Не удалось создать папку для хранения временных файлов.');
                }
                else {
                    $temp_fn = Yii::$app->security->generateRandomString(8) . '.zip';
                    $temp_ffp = $filepath . '/' . $temp_fn;

                    $zip = new \ZipArchive();
                    $zip->open($temp_ffp, \ZipArchive::CREATE);

                    $currentDirectory = '';
                    foreach ($files as $file) {
                        /* @var $file \common\models\FileStorage */

                        if ($currentDirectory != $file->ca_id) {
                            $zip->addEmptyDir($file->ca_id);
                        }

                        // проверяем физическое существование файла и соответствие выбранным типам файлов
                        if (file_exists($file->ffp) && in_array($file->type_id, $model->type_ids)) {
                            // файл существует, копируем его во временный файл, в котором будут производиться изменения
                            $zip->addFile($file->ffp, $file->ca_id . '/' . $file->ofn);
                        }

                        $currentDirectory = $file->ca_id;
                    }

                    $zip->close();
                    \Yii::$app->response->sendFile($temp_ffp, 'storage_extraction-' . $temp_fn, ['mimeType' => 'application/zip']);
                    if (file_exists($temp_ffp)) unlink($temp_ffp);
                }
            }
        }

        return $this->render('files_extraction', ['model' => $model]);
    }

    /**
     * Идентификация контрагента при изменении номера проекта.
     * @param $project_id integer идентификатор проекта
     * @return mixed
     */
    public function actionProjectOnChange($project_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $project = foProjects::findOne($project_id);
        if ($project) {
            return [
                'id' => $project->ID_COMPANY,
                'name' => $project->companyName,
            ];
        }

        return false;
    }

    /**
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionBulkImport()
    {
        $model = new StorageBulkImportForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $required = []; // массив документов, обязательных для некоторых контрагентов (проектов)
                $countSuccessFiles = 0; // количество файлов, которые обработаны успешно
                $errors = []; // здесь будем хранить ошибки, накопленные в процессе импорта
                $projectStates15 = []; // проекты, по которым подгружались файлы с признаком "несовпадение" от производства
                if ($model->validate()) {
                    $model->files = UploadedFile::getInstances($model, 'files');
                    foreach ($model->files as $file) {
                        $transaction = Yii::$app->db->beginTransaction();
                        $project_id = intval($file->name);
                        if (!empty($project_id) && $model->type_id == UploadingFilesMeanings::ТИП_КОНТЕНТА_ТТН) {
                            $project = foProjects::findOne($project_id);
                            if ($project) {
                                try {
                                    // отмечаем во Fresh Office что оригинал документа на руках
                                    $project->updateAttributes(['ADD_ttn' => 'да']);

                                    // помещаем файл в хранилище
                                    $ca_id = $project->ID_COMPANY;
                                    $ca_name = $project->companyName;
                                    // папка контрагента уже может быть в базе
                                    $storedFolder = FileStorageFolders::find()->where(['fo_ca_id' => $ca_id])->one();
                                    if ($storedFolder != null) {
                                        // у контрагента есть папка уже
                                        $folderName = $storedFolder->folder_name;
                                    }
                                    else {
                                        // контрагент наверное еще не проходил парсинг, поэтому папка еще не назначена,
                                        // но она может существовать на диске. Вместе с Сергеем Дружко проверим это.
                                        $folderExists = file_exists(FileStorage::ROOT_FOLDER . '/' . $ca_name);
                                        if ($folderExists) $folderName = $ca_name; else $folderName = $ca_id;
                                    }

                                    $fsModel = new FileStorage([
                                        'caFolderName' => $folderName,
                                        'ca_id' => $ca_id,
                                        'ca_name' => $ca_name,
                                        'type_id' => $model->type_id,
                                    ]);
                                    $pifp = $fsModel->getUploadsFilepath();
                                    $fn = Yii::$app->security->generateRandomString() . '.' . $file->extension;
                                    $ffp = $pifp . '/' . $fn;

                                    if ($file->saveAs($ffp)) {
                                        $fsModel->ffp = $ffp;
                                        $fsModel->fn = $fn;
                                        $fsModel->ofn = $file->name;
                                        $fsModel->size = filesize($ffp);
                                        $fsModel->scenario = 'bulk_import';
                                        $fsModel->file = true;
                                        if (!$fsModel->save()) {
                                            $details = '';
                                            foreach ($fsModel->errors as $error)
                                                foreach ($error as $detail)
                                                    $details .= '<p>' . $detail . '</p>';

                                            if (!empty($details)) $errors[] = $details;

                                            $transaction->rollBack();
                                        }
                                        else {
                                            // все необходимые действия выполнены успешно, наращиваем количество успешно обработанных файлов
                                            //$errors[] = 'Контрагент ' . $ca_id . ', ответственный ' . $project->companyManagerId . ', проект ' . $project_id;
                                            $countSuccessFiles++;
                                            $transaction->commit();

                                            // проверим наличие файлов от производства с признаком "несоответствие"
                                            if (ProductionFeedbackFiles::find()->where(['project_id' => $project_id])->andWhere('`action` = 1')->count() > 0) {
                                                $projectStates15[] = $project_id;
                                            }

                                            // проверим, является ли предоставление ТТН по данному контрагенту обязательным
                                            if (StorageTtnRequired::find()
                                                ->where(['type' => StorageTtnRequired::TYPE_КОНТРАГЕНТ, 'entity_id' => $ca_id])
                                                ->orWhere(['type' => StorageTtnRequired::TYPE_ОТВЕТСТВЕННЫЙ, 'entity_id' => $project->companyManagerId])
                                                ->orWhere(['type' => StorageTtnRequired::TYPE_ПРОЕКТ, 'entity_id' => $project_id])
                                                ->count() > 0) {
                                                $required[] = [
                                                    'project_id' => $project_id,
                                                    'manager_name' => $project->companyManagerName,
                                                    'ca_name' => $project->companyName,
                                                ];
                                            }
                                        }
                                    }
                                    else {
                                        $transaction->rollBack();
                                    }
                                } catch (\Exception $e) {
                                    $transaction->rollBack();
                                    throw $e;
                                }
                            }
                        }
                        else {
                            $errors[] = 'Проект не идентифицирован: ' . $file->name . '.';
                        }
                    }
                    if (isset($project)) unset($project);
                }

                $flashType = 'error';
                if (count($model->files) == $countSuccessFiles) {
                    $flashType = 'success';
                    $flashMessage = 'Все файлы успешно загружены.';

                    if (!empty($required)) {
                        if (!empty($projectStates15)) {
                            $required[] = [
                                'project_id' => ' ',
                                'manager_name' => ' ',
                                'ca_name' => ' ',
                            ];
                            $required[] = [
                                'project_id' => 'Несоответствия',
                                'manager_name' => ' ',
                                'ca_name' => ' ',
                            ];
                            foreach ($projectStates15 as $item) {
                                $required[] = [
                                    'project_id' => $item,
                                    'manager_name' => '-',
                                    'ca_name' => '-',
                                ];
                            }
                        }

                        Excel::export([
                            'models' => (new ArrayDataProvider([
                                'allModels' => $required,
                                'pagination' => false,
                                'sort' => false,
                            ]))->getModels(),
                            'fileName' => 'Список обязательных ТТН.xlsx',
                            'asAttachment' => true,
                            'columns' => [
                                [
                                    'attribute' => 'project_id',
                                    'header' => 'Проект',
                                ],
                                [
                                    'attribute' => 'manager_name',
                                    'header' => 'Менеджер',
                                ],
                                [
                                    'attribute' => 'ca_name',
                                    'header' => 'Контрагент',
                                ],
                            ],
                        ]);
                    }
                }
                else {
                    $flashMessage = 'Файлы не были загружены в полном объеме (' . $countSuccessFiles . ' / ' . count($model->files) . ').';
                    // выводим возможные ошибки
                    if (count($errors) > 0) {
                        foreach ($errors as $error) {
                            $flashMessage .= Html::tag('p', $error);
                        }
                    }
                }

                // выводим проекты, по которым зафиксировано несовпадение
                if (count($projectStates15)) {
                    $flashMessage .= Html::tag('p', 'Проекты, по которым зафиксировано несовпадение груза: ' . trim(implode(', ', $projectStates15), ', '));
                }

                if (!empty($flashMessage)) {
                    Yii::$app->session->setFlash($flashType, $flashMessage);

                    return $this->redirect(['/storage/bulk-import']);
                }
            }
        }
        else {
            $model->type_id = UploadingFilesMeanings::ТИП_КОНТЕНТА_ТТН;
        }

        return $this->render('bulk_import', ['model' => $model]);
    }
}
