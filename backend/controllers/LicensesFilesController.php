<?php

namespace backend\controllers;

use common\models\LicensesFkkoPages;
use Yii;
use common\models\LicensesFiles;
use common\models\LicensesFilesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * LicensesFilesController implements the CRUD actions for LicensesFiles model.
 */
class LicensesFilesController extends Controller
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
                        'roles' => ['root', 'licenses_upload'],
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

    /**
     * Lists all LicensesFiles models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LicensesFilesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new LicensesFiles model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new LicensesFiles();

        if ($model->load(Yii::$app->request->post())) {
            $model->importFile = UploadedFile::getInstance($model, 'importFile');
            // вычислим путь к папке для загрузки файлов
            $uploadsFilepath = LicensesFiles::getUploadsFilepath();
            if (false !== $uploadsFilepath) {
                // формируем полный путь с именем файла
                $fn = Yii::$app->security->generateRandomString() . '.' . $model->importFile->extension;
                $ffp = $uploadsFilepath . '/' . $fn;
                if ($model->upload($ffp)) {
                    $model->ffp = $ffp;
                    $model->fn = $fn;
                    $model->ofn = $model->importFile->name;
                    $model->size = filesize($ffp);

                    // если файл по сформированному пути удалось загрузить на сервер и успешно на нем сохранить
                    if ($model->validate() && $model->save()) {
                        // если удалось сохранить модель, то создадим необходимое количество записей кодов ФККО
                        foreach ($model->tpFkkos as $fkkoPage) {
                            /* @var $fkkoPage LicensesFkkoPages */
                            $fkkoPage->file_id = $model->id;
                            $fkkoPage->save();
                        }

                        return $this->redirect(['/licenses-files']);
                    }
                }
            }
            else
                $model->addError('importFile', 'Невозможно создать папку для хранения загруженных файлов!');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing LicensesFiles model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/licenses-files']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing LicensesFiles model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->checkIfUsed())
            return $this->render('/common/cannot_delete', [
                'details' => [
                    'breadcrumbs' => ['label' => 'Сканы лицензий', 'url' => ['/licenses-files']],
                    'modelRep' => $model->ofn,
                    'buttonCaption' => 'Сканы лицензий',
                    'buttonUrl' => ['/licenses-files'],
                    'action1' => 'удалить',
                    'action2' => 'удален',
                ],
            ]);

        $model->delete();

        return $this->redirect(['/licenses-files']);
    }

    /**
     * Finds the LicensesFiles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return LicensesFiles the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LicensesFiles::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
