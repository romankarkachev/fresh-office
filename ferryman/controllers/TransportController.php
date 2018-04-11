<?php

namespace ferryman\controllers;

use Yii;
use common\models\Transport;
use common\models\TransportSearch;
use common\models\TransportFiles;
use common\models\UploadingFilesMeanings;
use common\models\Ferrymen;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * TransportController implements the CRUD actions for Transport model.
 */
class TransportController extends Controller
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
                        ],
                        'allow' => true,
                        'roles' => ['root', 'ferryman'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Transport models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, ['updated_at' => SORT_DESC], 'transport');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Transport model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Transport();

        if ($model->load(Yii::$app->request->post())) {
            // если запрос выполняет перевозчик, то заполним поле "Перевозчик" текущей модели
            if (Yii::$app->user->can('ferryman')) {
                $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
                // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
                if ($ferryman != null) {
                    $model->ferryman_id = $ferryman->id;
                    if ($model->save()) return $this->redirect(['/transport/update', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Transport model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // файлы со сканами документов водителя
            $errors = '';

            $model->fileOsago = UploadedFile::getInstance($model, 'fileOsago');
            if ($model->fileOsago != null)
                if (!$model->upload('fileOsago', $model->fileOsago->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО))
                    $errors .= '<p>Главный разворот паспорта не был успешно загружен.</p>';

            $model->filePtsFace = UploadedFile::getInstance($model, 'filePtsFace');
            if ($model->filePtsFace != null)
                if (!$model->upload('filePtsFace', $model->filePtsFace->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ))
                    $errors .= '<p>Регистрация по месту жительства в паспорте не была успешно загружена.</p>';

            $model->filePtsReverse = UploadedFile::getInstance($model, 'filePtsReverse');
            if ($model->filePtsReverse != null)
                if (!$model->upload('filePtsReverse', $model->filePtsReverse->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ))
                    $errors .= '<p>Лицевая сторона водительского удостоверения не была успешно загружена.</p>';

            $model->fileStsFace = UploadedFile::getInstance($model, 'fileStsFace');
            if ($model->fileStsFace != null)
                if (!$model->upload('fileStsFace', $model->fileStsFace->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ))
                    $errors .= '<p>Оборотная сторона водительского удостоверения не была успешно загружена.</p>';

            $model->fileStsReverse = UploadedFile::getInstance($model, 'fileStsReverse');
            if ($model->fileStsReverse != null)
                if (!$model->upload('fileStsReverse', $model->fileStsReverse->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ))
                    $errors .= '<p>Оборотная сторона водительского удостоверения не была успешно загружена.</p>';

            $model->fileDk = UploadedFile::getInstance($model, 'fileDk');
            if ($model->fileDk != null)
                if (!$model->upload('fileDk', $model->fileDk->name, UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА))
                    $errors .= '<p>Оборотная сторона водительского удостоверения не была успешно загружена.</p>';

            if ($errors != '') Yii::$app->session->setFlash('error', $errors);
            // -- файлы

            if ($model->save()) return $this->redirect(['/transport']);
        }

        // файлы конкретных типов
        $files = TransportFiles::find()->select(['id', 'ufm_id', 'fn', 'ofn'])->where(['transport_id' => $id, 'ufm_id' => [
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ,
            UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА,
        ]])->asArray()->all();

        return $this->render('update', [
            'model' => $model,
            'files' => $files,
        ]);
    }

    /**
     * Deletes an existing Transport model.
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

        return $this->redirect(['/transport']);
    }

    /**
     * Finds the Transport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Transport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Transport::findOne($id)) !== null) {
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
        $upload_path = TransportFiles::getUploadsFilepath();
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
                    $fu = new TransportFiles();
                    $fu->transport_id = $obj_id;
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
        $model = TransportFiles::findOne(['id' => $id]);
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
            $model = TransportFiles::findOne($id);
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
        $model = TransportFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->transport_id;
            $model->delete();

            return $this->redirect(['/transport/update', 'id' => $record_id]);
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
        $model = TransportFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return Html::img(Yii::getAlias('@uploads-ferrymen-transport') . '/' . $model->fn, ['width' => 600]);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/transport/download-from-outside', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }
}
