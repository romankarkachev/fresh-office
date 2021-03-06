<?php

namespace backend\controllers;

use Yii;
use common\models\Drivers;
use common\models\DriversSearch;
use common\models\DriversFiles;
use common\models\DriversFilesSearch;
use common\models\UploadingFilesMeanings;
use common\models\User;
use backend\models\FerrymanReplacingForm;
use dektrium\user\helpers\Password;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * DriversController implements the CRUD actions for Drivers model.
 */
class FerrymenDriversController extends Controller
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
                        'actions' => ['download-from-outside'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update', 'delete',
                            'upload-files', 'download-file', 'preview-file', 'delete-file',
                            'create-user',
                            'replace-ferryman-form', 'validate-ferryman-replacing', 'replace-ferryman',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'head_assist'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'create-user' => ['POST'],
                    'validate-ferryman-replacing' => ['POST'],
                    'replace-ferryman' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Drivers models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DriversSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('/drivers/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new Drivers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Drivers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/ferrymen-drivers/update', 'id' => $model->id]);
        } else {
            return $this->render('/drivers/create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Drivers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // файлы со сканами документов водителя
            $filesProvided = false;
            $filesSuccessfullyLoaded = true;
            $errors = '';

            $model->filePassportFace = UploadedFile::getInstance($model, 'filePassportFace');
            if ($model->filePassportFace != null) {
                $filesProvided = true;
                if (!$model->upload('filePassportFace', $model->filePassportFace->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ)) {
                    $filesSuccessfullyLoaded = false;
                    $errors .= '<p>Главный разворот паспорта не был успешно загружен.</p>';
                }
            }

            $model->filePassportReverse = UploadedFile::getInstance($model, 'filePassportReverse');
            if ($model->filePassportReverse != null) {
                $filesProvided = true;
                if (!$model->upload('filePassportReverse', $model->filePassportReverse->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА)) {
                    $filesSuccessfullyLoaded = false;
                    $errors .= '<p>Регистрация по месту жительства в паспорте не была успешно загружена.</p>';
                }
            }

            $model->fileDlFace = UploadedFile::getInstance($model, 'fileDlFace');
            if ($model->fileDlFace != null) {
                $filesProvided = true;
                if (!$model->upload('fileDlFace', $model->fileDlFace->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ)) {
                    $filesSuccessfullyLoaded = false;
                    $errors .= '<p>Лицевая сторона водительского удостоверения не была успешно загружена.</p>';
                }
            }

            $model->fileDlReverse = UploadedFile::getInstance($model, 'fileDlReverse');
            if ($model->fileDlReverse != null) {
                $filesProvided = true;
                if (!$model->upload('fileDlReverse', $model->fileDlReverse->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ)) {
                    $filesSuccessfullyLoaded = false;
                    $errors .= '<p>Оборотная сторона водительского удостоверения не была успешно загружена.</p>';
                }
            }

            if ($errors != '')
                Yii::$app->session->setFlash('error', $errors);
            else if ($filesProvided && $filesSuccessfullyLoaded)
                Yii::$app->session->setFlash('success', 'Файлы успешно загружены.');
            // -- файлы

            if ($model->save()) return $this->redirect(['/ferrymen-drivers/update', 'id' => $model->id]);
        }

        $params = ['model' => $model];

        // файлы к объекту
        $searchModel = new DriversFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['driver_id' => $model->id]]);
        $dpFiles->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dpFiles->pagination = false;
        $params['dpFiles'] = $dpFiles;

        // файлы конкретных типов
        $files = DriversFiles::find()->select(['id', 'ufm_id', 'fn', 'ofn'])->where(['driver_id' => $id, 'ufm_id' => [
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ
        ]])->asArray()->all();
        $params['files'] = $files;

        return $this->render('/drivers/update', $params);
    }

    /**
     * Deletes an existing Drivers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('root')) {
            // для пользователей с ограниченными правами только лишь помечаем на удаление
            $model = $this->findModel($id);
            $model->is_deleted = true;
            $model->save(false);
        }
        else $this->findModel($id)->delete();

        return $this->redirect(['/ferrymen-drivers']);
    }

    /**
     * Finds the Drivers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Drivers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Drivers::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = DriversFiles::getUploadsFilepath($obj_id);
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
                    $fu = new DriversFiles();
                    $fu->driver_id = $obj_id;
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
    public function actionDownloadFromOutside($id)
    {
        $model = DriversFiles::findOne(['id' => $id]);
        if (file_exists($model->ffp))
            return Yii::$app->response->sendFile($model->ffp, $model->ofn);
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
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
            $model = DriversFiles::findOne($id);
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
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDeleteFile($id)
    {
        $model = DriversFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->driver_id;
            $model->delete();

            return $this->redirect(['/ferrymen-drivers/update', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Выполняет предварительный показ изображения.
     * @param $id integer идентификатор файла, который необходимо предварительно показать
     * @return mixed
     */
    public function actionPreviewFile($id)
    {
        $model = DriversFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return Html::img(Yii::getAlias('@uploads-ferrymen-drivers') . '/' . $model->fn, ['width' => 600]);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/ferrymen-drivers/download-from-outside', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Выполняет создание учетной записи пользователя с ролью "Водитель перевозчика" и привязывает ее к водителю,
     * переданному в параметрах, в случае успеха.
     * @param $driver_id integer водитель, на основании которого создается пользователь
     */
    public function actionCreateUser($driver_id)
    {
        /** @var $user User */
        $driver = Drivers::findOne($driver_id);
        if ($driver) {
            if (empty($driver->user_id)) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $success = false;
                    $user = \Yii::createObject(['class' => User::className()]);
                    $user->username = Drivers::FOREIGN_DRIVER_LOGIN_PREFIX . $driver->id;
                    $user->confirmed_at = time();
                    $user->email = $user->username . '@gmail.com';
                    $user->password = Password::generate(8);
                    if ($user->save()) {
                        // обновим профиль, ФИО водителя послужит для заполнения
                        // mb_convert_case(<ФИО>, MB_CASE_TITLE) преобразует все в нижний регистр, а потом у каждого слова повышает регистр первого символа
                        $user->profile->name = mb_convert_case(trim(implode(' ', [
                            trim(trim(trim($driver->surname, ', '), ',')),
                            trim(trim(trim($driver->name, ', '), ',')),
                            trim(trim(trim($driver->patronymic, ', '), ',')),
                        ])), MB_CASE_TITLE);
                        if ($user->profile->save()) {
                            // привязываем роль
                            $role = Yii::$app->authManager->getRole(Drivers::FOREIGN_DRIVER_ROLE_NAME);
                            Yii::$app->authManager->assign($role, $user->id);

                            // привязываем новую учетную запись к водителю
                            $success = $driver->updateAttributes([
                                    'user_id' => $user->id
                                ]) > 0;
                        } else $transaction->rollback();
                    } else $transaction->rollback();

                    if ($success) {
                        $transaction->commit();
                        $a = Html::a('здесь', ['users/update', 'id' => $user->id], ['target' => '_blank', 'title' => 'Открыть созданный аккаунт пользователя в новом окне']);
                        Yii::$app->session->setFlash('success', 'Пользователь успешно создан и активирован. Теперь он может авторизоваться под именем &laquo;' . $user->username . '&raquo; и паролем &laquo;' . $user->password . '&raquo;. <strong>Внимание!</strong> После обновления этой страницы пароль исчезнет, скопируйте и передайте его водителю прямо сейчас иначе придется назначать новый через управление аккаунтом этого пользователя ' . $a . '!');
                    } else {
                        Yii::$app->session->setFlash('error', 'Не удалось создать пользователя.');
                    }
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', 'Создание пользователя на основании водителя завершилсь с ошибкой: ' . $e->getMessage());
                    $transaction->rollback();
                }
            }
            else Yii::$app->session->setFlash('error', 'Водитель уже привязан к пользователю.');
        }
        else Yii::$app->session->setFlash('error', 'Не обнаружен водитель.');

        $this->redirect(['/ferrymen-drivers/update', 'id' => $driver->id]);
    }

    /**
     * Рендерит форму приглашения перевозчика создать аккаунт в личном кабинете.
     * @param $id integer идентификатор перевозчика, который приглашается
     * @return mixed
     */
    public function actionReplaceFerrymanForm($id)
    {
        if (Yii::$app->request->isAjax) {
            $id = intval($id);
            $driver = Drivers::findOne($id);
            if ($driver) {
                return $this->renderAjax('/drivers/_replace_ferryman_form', [
                    'model' => new FerrymanReplacingForm([
                        'driver_id' => $id,
                    ]),
                ]);
            }
        }

        return false;
    }

    /**
     * AJAX-валидация формы отправки приглашения.
     */
    public function actionValidateFerrymanReplacing()
    {
        $model = new FerrymanReplacingForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return Json::encode(\yii\widgets\ActiveForm::validate($model));
        }
    }

    /**
     * Отправляет приглашение перевозчику создать аккаунт в личном кабинете.
     * @return array|bool
     */
    public function actionReplaceFerryman()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new FerrymanReplacingForm();

        if ($model->load(Yii::$app->request->post())) {
            return $model->replaceFerryman();
        }

        return false;
    }
}
