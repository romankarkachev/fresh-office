<?php

namespace backend\controllers;

use Yii;
use common\models\FileStorage;
use common\models\FileStorageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * StorageController implements the CRUD actions for FileStorage model.
 */
class StorageController extends Controller
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
                        'actions' => ['index', 'create', 'update'],
                        'allow' => true,
                        'roles' => ['root'],
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
                ],
            ],
        ];
    }

    public function actionScanDir()
    {
        
    }

    /**
     * Lists all FileStorage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FileStorageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
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

        if ($model->load(Yii::$app->request->post())) {
            $pifp = FileStorage::getUploadsFilepath();
            $file = UploadedFile::getInstance($model, 'file');
            if ($file != null) {
                $fn = Yii::$app->security->generateRandomString() . '.' . $file->extension;
                $ffp = $pifp . '/' . $fn;
                // делаем запись в базу о нем
                $model->ffp = $ffp;
                $model->fn = $fn;
                $model->ofn = $file->name;

                // сохраняем основное изображение
                if ($file->saveAs($ffp)) {
                    $model->size = filesize($ffp);
                    $model->file = true; // помечаем, что файл предоставлен, чтобы успешно пройти валидацию
                    if ($model->save())
                        return $this->redirect(['/storage']);
                    else
                        // удалим загруженный файл
                        if (file_exists($ffp)) unlink($ffp);
                }
                else
                    $model->addError('file', 'Не удалось загрузить файл на сервер.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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
     * Deletes an existing FileStorage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/storage']);
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
}
