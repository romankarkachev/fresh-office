<?php

namespace backend\controllers;

use Yii;
use common\models\CorrespondencePackages;
use common\models\CorrespondencePackagesSearch;
use common\models\CorrespondencePackagesFiles;
use common\models\CorrespondencePackagesFilesSearch;
use common\models\CounteragentsPostAddresses;
use common\models\PostDeliveryKinds;
use common\models\ProjectsStates;
use common\models\ComposePackageForm;
use common\models\PadKinds;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * CorrespondencePackagesController implements the CRUD actions for CorrespondencePackages model.
 */
class CorrespondencePackagesController extends Controller
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
                        'actions' => ['download-file'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update',
                            'compose-package-form', 'compose-package', 'create-address-form',
                            'upload-files', 'preview-file', 'delete-file',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'operator_head'],
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
     * Lists all CorrespondencePackages models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CorrespondencePackagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new CorrespondencePackages model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CorrespondencePackages();
        $model->pad = $model->convertPadTableToArray();

        if ($model->load(Yii::$app->request->post())) {
            $model->is_manual = true; // всегда, созданные роботом отмечаются противоположным признаком
            $model->pad = $model->convertPadTableToArray();

            if ($model->save()) return $this->redirect(['/correspondence-packages']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CorrespondencePackages model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->pad = $model->convertPadTableToArray();

            if ($model->save()) return $this->redirect(['/correspondence-packages']);
        }

        // файлы к объекту
        $searchModel = new CorrespondencePackagesFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['cp_id' => $model->id]]);
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
     * Deletes an existing CorrespondencePackages model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/correspondence-packages']);
    }

    /**
     * Finds the CorrespondencePackages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CorrespondencePackages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CorrespondencePackages::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Формирует и отдает форму назначения трек-номера, статуса, выборка видов документов.
     * @param $ids string идентификаторы проектов
     * @return mixed
     */
    public function actionComposePackageForm($ids)
    {
        if (Yii::$app->request->isAjax) {
            $model = new ComposePackageForm();
            $model->project_ids = explode(',', $ids);
            $model->tpPad = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new \yii\db\Expression(0)])->orderBy('name_full')->asArray()->all();

            return $this->renderAjax('_compose_package_form', [
                'model' => $model,
            ]);
        }

        return '';
    }

    /**
     * Назначает виды документов, способ доставки, статус пакета документов и трек-номер (при необходимости).
     */
    public function actionComposePackage()
    {
        Url::remember(Yii::$app->request->referrer);
        if (Yii::$app->request->isPost) {
            $model = new ComposePackageForm();
            if ($model->load(Yii::$app->request->post())) {
                $packages = CorrespondencePackages::find()->where(['in', 'id', $model->project_ids])->all();
                foreach ($packages as $package) {
                    /* @var $package CorrespondencePackages */
                    $package->state_id = ProjectsStates::STATE_ОТПРАВЛЕНО;
                    $package->tpPad = $model->tpPad;
                    $package->pad = $package->convertPadTableToArray();
                    $package->pd_id = $model->pd_id;
                    $package->track_num = '';
                    if ($package->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ ||
                        $package->pd_id == PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS)
                        $package->track_num = $model->track_num;

                    $package->save();
                }
                $this->goBack();
            }
        }
    }

    /**
     * Формирует и отдает форму добавления нового почтового адреса контрагента.
     * @param $id integer идентификатор пакета корреспонденции
     * @param $ca_id integer идентификатор контрагента
     * @return mixed
     */
    public function actionCreateAddressForm($id, $ca_id)
    {
        $model = new CounteragentsPostAddresses();

        if ($model->load(Yii::$app->request->post())) {
            $ca_id = intval($ca_id);
            $cp = $this->findModel(intval($id));
            $cp->pad = $cp->convertPadTableToArray();

            if ($ca_id > 0 && $cp != null)
                //if ($model->save()) print '<p>Все нормально.</p>';
                return $this->render('update', [
                    'model' => $cp,
                ]);
        }

        if (Yii::$app->request->isAjax && $ca_id > 0) {
            $model->counteragent_id = $ca_id;
            return $this->renderAjax('/counteragents-post-addresses/_form', [
                'model' => $model,
            ]);
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
        $upload_path = CorrespondencePackagesFiles::getUploadsFilepath();
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
                    $fu = new CorrespondencePackagesFiles();
                    $fu->cp_id = $obj_id;
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
            $model = CorrespondencePackagesFiles::findOne($id);
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
        $model = CorrespondencePackagesFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return \yii\helpers\Html::img(Yii::getAlias('@uploads-correspondence-packages') . '/' . $model->fn, ['width' => '100%']);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/correspondence-packages/download-file', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если файл не будет обнаружен
     */
    public function actionDeleteFile($id)
    {
        $model = CorrespondencePackagesFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->cp_id;
            $model->delete();

            return $this->redirect(['/correspondence-packages/update', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }
}
