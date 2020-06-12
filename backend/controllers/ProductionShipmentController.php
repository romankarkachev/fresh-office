<?php

namespace backend\controllers;

use Yii;
use common\models\ProductionShipment;
use common\models\ProductionShipmentSearch;
use common\models\ProductionShipmentFiles;
use common\models\ProductionShipmentFilesSearch;
use common\models\ResponsibleForProduction;
use common\models\FreshOfficeAPI;
use common\models\Transport;
use common\models\foProjects;
use common\models\ProjectsStates;
use common\models\ProjectsTypes;
use common\models\FerrymenPrices;
use common\models\foProjectsGoods;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ProductionShipmentController implements the CRUD actions for ProductionShipment model.
 */
class ProductionShipmentController extends Controller
{
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
                        'actions' => [
                            'index', 'create', ProductionShipment::URL_UPDATE, ProductionShipment::URL_IDENTIFY_TRANSPORT,
                            ProductionShipment::URL_RENDER_FILES,
                            ProductionShipment::URL_DOWNLOAD_FILE, ProductionShipment::URL_UPLOAD_FILES, ProductionShipment::URL_PREVIEW_FILE, ProductionShipment::URL_DELETE_FILE,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'prod_department_head'],
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
                    ProductionShipment::URL_DELETE_FILE => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку файлов, приаттаченных пользователями системы к отправке.
     * @param $params integer|array
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchFiles($params)
    {
        $searchModel = new ProductionShipmentFilesSearch();
        if (is_numeric($params)) {
            $params = [$searchModel->formName() => ['ps_id' => $params]];
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Рендерит список файлов, прикрепленных к отправке.
     * @param integer $id идентификатор отправки
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderFiles($id)
    {
        return $this->renderAjax('_files_list', ['dataProvider' => $this->fetchFiles($id)]);
    }

    /**
     * По номеру транспортного средства выполняется попытка его идентифицировать.
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function identifyTransport($rn)
    {
        $rnIndex = \common\behaviors\IndexFieldBehavior::processValue($rn);
        return Transport::find()->where(['rn_index' => $rnIndex])->one();
    }

    /**
     * Lists all ProductionShipment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductionShipmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new ProductionShipment model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = new ProductionShipment();

        if ($model->load(Yii::$app->request->post())) {
            $success = true;
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($model->save()) {
                    $model->crudeFiles = UploadedFile::getInstances($model, 'crudeFiles');
                    if (count($model->crudeFiles) > 0) {
                        $files = $model->upload();
                        if (empty($files) || count($model->crudeFiles) != count($files)) {
                            // отправили туда массив с файлами, вернулся пустой или не с таким же количеством элементов
                            Yii::$app->session->setFlash('error', 'При загрузке файлов произошла ошибка. Вам придется выбрать их еще раз.');
                            // отменяем транзакцию
                            $transaction->rollBack();
                            // удаляем файлы
                            foreach ($files as $file) { FileHelper::unlink($file); }
                        }
                    }
                }
                else {
                    $success = false;
                }
            }
            catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            catch(\Throwable $e) {
                $transaction->rollBack();
            }

            if ($success) {
                $transaction->commit();
                return $this->redirect([ProductionShipment::URL_ROOT_ROUTE . '/' . ProductionShipment::URL_UPDATE, 'id' => $model->id]);
            }
        }

        if (!empty($model->rn)) {
            $model->transport_id = $this->identifyTransport($model->rn);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProductionShipment model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $summaryMessage = [];

                if (null !== Yii::$app->request->post($model::BUTTON_SUBMIT_SEND_NAME)) {
                    // отправляем письмо заинтересованным лицам
                    $letter = Yii::$app->mailer->compose([
                        'html' => 'productionShipment-html',
                    ], ['body' => $model->comment])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameHeinrich']])
                        ->setSubject($model->subject);

                    $files = $model->productionShipmentFiles;
                    if (count($files) > 0) foreach ($files as $file) $letter->attach($file->ffp, ['fileName' => $file->ofn]);

                    // отправка писем обязательным получателям
                    $sCount = 0;
                    foreach (ResponsibleForProduction::find()->where(['type' => ResponsibleForProduction::TYPE_SHIPMENT])->all() as $receiver) {
                        /* @var $receiver ResponsibleForProduction */

                        $email = $letter;
                        $email->setTo($receiver->receiver);
                        // если почта не отправится (из-за проблем с провайдером, например), то статус закрыть все равно нужно
                        try { $email->send() ? $sCount++ : null; } catch (\Exception $exception) {}

                        unset($email);
                    }

                    if ($sCount > 0) {
                        $summaryMessage[] = 'Все письма заинтересованным лицам успешно отправлены.';
                    }
                    else {
                        $summaryMessage[] = 'При отправке писем заинтересованным лицам возникли трудности.';
                    }

                    // создаем проект и в случае успеха сохраняем его идентификатор
                    $foCompanyManager = $model->site->companyManager;
                    $response = FreshOfficeAPI::makePostRequestToApi('project', [
                        'date_create_project' => Yii::$app->formatter->asDate(time(), 'php:Y-m-d\TH:i:s'),
                        'id_company' => $model->site->fo_ca_id,
                        'id_manager' => $foCompanyManager->ID_MANAGER,
                        'id_list_project' => ProjectsTypes::PROJECT_TYPE_ПРОИЗВОДСТВО,
                        'id_priznak_project' => ProjectsStates::STATE_ЗАВЕРШЕНО,
                        'id_manager_ver' => $foCompanyManager->ID_MANAGER,
                        'id_manager_creator' => $foCompanyManager->ID_MANAGER,
                        'prim_project_company' => 'Проект создан при отправке техники с производственной площадки',
                    ]);

                    // проанализируем результат, который возвращает API Fresh Office
                    $decodedResponse = Json::decode($response, true);
                    if (isset($decodedResponse['error'])) {
                        // средствами API проект создать не удалось
                        $inner_message = '';
                        if (isset($decodedResponse['error']['innererror'])) {
                            $inner_message = ' ' . $decodedResponse['error']['innererror']['message'];
                        }

                        // возникла ошибка при выполнении
                        $summaryMessage[] = 'При создании проекта возникла ошибка: ' . $decodedResponse['error']['message']['value'] . $inner_message;
                        unset($inner_message);
                    }
                    elseif (isset($decodedResponse['d'])) {
                        // фиксируем идентификатор проекта, который был успешно создан
                        $projectId = intval($decodedResponse['d']['id_list_project_company']);
                        $project = foProjects::findOne(['ID_LIST_PROJECT_COMPANY' => $projectId]);

                        $model->updateAttributes([
                            'fo_project_id' => $projectId,
                        ]);

                        $projectAttributes = [
                            'ADD_ttn' => 'Производство',
                            'ADD_vivozdate' => Yii::$app->formatter->asDate(time(), 'php:Y-m-d\T00:00:00'),
                        ];

                        if (!empty($model->transport)) {
                            $projectAttributes = ArrayHelper::merge($projectAttributes, [
                                'ADD_perevoz' => $model->ferrymanCrmName,
                                'ADD_dannie' => $model->transportRep,
                            ]);
                        }

                        $project->updateAttributes($projectAttributes);

                        // попытаемся найти цены этого перевозчика
                        $ferryman = $model->ferryman;
                        if (!empty($ferryman)) {
                            $ferrymanPrices = FerrymenPrices::findOne(['ferryman_id' => $ferryman->id]);
                            // добавляем товары в проект
                            if (!empty($ferrymanPrices)) {
                                (new foProjectsGoods([
                                    'ID_LIST_PROJECT_COMPANY' => $projectId,
                                    'DISCRIPTION_TOVAT' => $model->transportRep,
                                    'PRICE_TOVAR' => $ferrymanPrices->price,
                                    'ED_IZM_TOVAR' => 'услуга',
                                    'SS_PRICE_TOVAR' => $ferrymanPrices->cost,
                                    'KOLVO' => 1,
                                ]))->save();
                            }
                        }
                    }
                }

                if (count($summaryMessage) > 0) {
                    $message = '';
                    foreach ($summaryMessage as $sm) {
                        $message .= '<p>' . $sm . '</p>';
                    }
                    Yii::$app->session->setFlash('info', $message);
                }

                return $this->redirect(ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'dpFiles' => $this->fetchFiles($id),
        ]);
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = intval(Yii::$app->request->post('obj_id'));
        $object = ProductionShipment::findOne($obj_id);
        if ($object) {
            $upload_path = ProductionShipmentFiles::getUploadsFilepath($object);
            if (false === $upload_path) return 'Невозможно создать папку для хранения загруженных файлов!';

            // массив загружаемых файлов
            $files = $_FILES['files'];
            // массив имен загружаемых файлов
            $filenames = $files['name'];
            if (count($filenames) > 0) {
                for ($i = 0; $i < count($filenames); $i++) {
                    // идиотское действие, но без него
                    // PHP Strict Warning: Only variables should be passed by reference
                    $tmp = explode('.', basename($filenames[$i]));
                    $ext = end($tmp);
                    $filename = mb_strtolower(Yii::$app->security->generateRandomString() . '.' . $ext, 'utf-8');
                    $filepath = $upload_path . '/' . $filename;
                    if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                        $fu = new ProductionShipmentFiles();
                        $fu->ps_id = $obj_id;
                        $fu->ffp = $filepath;
                        $fu->fn = $filename;
                        $fu->ofn = $filenames[$i];
                        $fu->size = filesize($filepath);
                        if ($fu->validate()) $fu->save(); else return 'Загруженные данные неверны.';
                    };
                };
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
            $model = ProductionShipmentFiles::findOne($id);
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
        $model = ProductionShipmentFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return \yii\helpers\Html::img(Yii::getAlias('@uploads-ps') . '/' . $model->ps_id . '/' . $model->fn, ['width' => '100%']);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . ProductionShipment::URL_ROOT . '/' . ProductionShipment::URL_DOWNLOAD_FILE, 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param $id
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFile($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = ProductionShipmentFiles::findOne($id);
            if ($model) {
                $parent_id = $model->ps_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderFiles($parent_id);
                    }
                    else {
                        $transaction->rollBack();
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }
    }

    /**
     * Рендерит список файлов.
     * @param $id integer отправка, файлы которой необходимо отрендерить
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRenderFiles($id)
    {
        return $this->renderFiles($id);
    }

    /**
     * Deletes an existing ProductionShipment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY);
    }

    /**
     * Finds the ProductionShipment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductionShipment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductionShipment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::$app->params['promptPageNotFound']);
    }

    /**
     * Попытка идентифицировать транспортное средство по номеру.
     * @param $rn
     * @return array|bool
     */
    public function actionIdentifyTransport($rn)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->identifyTransport($rn);
        if ($model) {
            return [
                'result' => true,
                'transport_id' => $model->id,
                'transportRep' => $model->transportRep,
            ];
        }

        return false;
    }
}
