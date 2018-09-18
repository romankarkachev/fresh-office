<?php

namespace backend\controllers;

use Yii;
use common\models\Ferrymen;
use common\models\FerrymenSearch;
use common\models\DirectMSSQLQueries;
use common\models\FerrymenBankCards;
use common\models\FerrymenBankCardsSearch;
use common\models\FerrymenBankDetails;
use common\models\FerrymenBankDetailsSearch;
use common\models\Drivers;
use common\models\DriversSearch;
use common\models\Transport;
use common\models\TransportSearch;
use common\models\DriversInstructings;
use common\models\DriversInstructingsSearch;
use common\models\TransportInspections;
use common\models\TransportInspectionsSearch;
use common\models\FerrymenFiles;
use common\models\FerrymenFilesSearch;
use backend\models\FerrymanInvitationForm;
use common\models\FerrymenInvitations;
use common\models\PaymentOrdersSearch;
use common\models\foProjects;
use common\models\foProjectsSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * FerrymenController implements the CRUD actions for Ferrymen model.
 */
class FerrymenController extends Controller
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
                        'actions' => ['download-from-outside', 'temp'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update', 'delete',
                            'missing-drivers-transport', 'get-duration-for-route',
                            'create-bank-account', 'delete-bank-account', 'create-bank-card', 'delete-bank-card',
                            'create-driver', 'delete-driver', 'create-transport', 'delete-transport',
                            'upload-files', 'download-file', 'preview-file', 'delete-file',
                            'drivers-instructings', 'create-instructing', 'delete-instructing',
                            'transports-inspections', 'create-inspection', 'delete-inspection',
                            'list-of-packing-ferrymen-for-typeahead', 'validate-ati-code',
                            'invite-ferryman-form', 'validate-invitation', 'send-invitation',
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
                    'create-bank-account' => ['POST'],
                    'delete-bank-account' => ['POST'],
                    'create-bank-card' => ['POST'],
                    'delete-bank-card' => ['POST'],
                    'create-driver' => ['POST'],
                    'delete-driver' => ['POST'],
                    'create-transport' => ['POST'],
                    'delete-transport' => ['POST'],
                    'create-instructing' => ['POST'],
                    'delete-instructing' => ['POST'],
                    'create-inspection' => ['POST'],
                    'delete-inspection' => ['POST'],
                    'validate-invitation' => ['POST'],
                    'send-invitation' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Ferrymen models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FerrymenSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionMissingDriversTransport()
    {
        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->searchMissingDriversTransport(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('missing_drivers_transport', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * @param $project_id integer идентификатор проекта, адрес которого вычисляется
     * @return mixed
     */
    public function actionGetDurationForRoute($project_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $project = foProjects::find()->where(['ID_LIST_PROJECT_COMPANY' => $project_id])->one();
            if ($project) {
                $ferryman_id = -1;
                $transport_found = -1;
                $driver_found = -1;

                if (!empty($project['ADD_dannie'])) {
                    $ferryman = Ferrymen::findOne(['name_crm' => $project['ADD_perevoz']]);
                    if ($ferryman) {
                        $ferryman_id = $ferryman->id;
                        $transport_found = false;
                        $driver_found = false;
                        $data = str_replace(chr(32), '', mb_strtolower($project['ADD_dannie']));
                        $data = str_replace('/', '', $data);
                        $data = str_replace('\\', '', $data);

                        // перевозчик идентифицирован, теперь попробуем найти у него такой транспорт
                        foreach ($ferryman->transport as $transport) {
                            if (false !== stripos($data, $transport->rn_index)) {
                                // транспорт такой наден, зафиксируем это
                                $transport_found = true;
                                break;
                            }
                        }

                        // найдем водителя
                        foreach ($ferryman->drivers as $driver) {
                            $driverName = mb_strtolower(trim($driver->surname) . trim($driver->name));
                            $driverSurname = mb_strtolower(trim($driver->surname) . trim($driver->name) . trim($driver->patronymic));
                            if (false !== stripos($data, $driver->driver_license_index) || false !== stripos($data, $driverName) || false !== stripos($data, $driverSurname)) {
                                // водитель такой наден, зафиксируем это
                                $driver_found = true;
                                break;
                            }
                        }
                    }
                }

                return [
                    'project_id' => $project_id,
                    'ferryman_id' => $ferryman_id,
                    'transport_found' => $transport_found,
                    'driver_found' => $driver_found,
                ];
            }
        }

        return false;
    }

    /**
     * Creates a new Ferrymen model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ferrymen();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // создадим перевозчика
            if (!DirectMSSQLQueries::createFerryman($model))
                Yii::$app->session->setFlash('error', 'В CRM Fresh Office не удалось создать перевозчика. Откройте созданного перевозчика и сохраните созданный элемент еще раз.');

            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            $model->post = 'Диспетчер';
            $model->post_dir = 'Руководитель';
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Ferrymen model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->fo_id == null) {
                // создадим перевозчика
                if (!DirectMSSQLQueries::createFerryman($model))
                    Yii::$app->session->setFlash('error', 'В CRM Fresh Office не удалось создать перевозчика. Откройте созданного перевозчика и сохраните созданный элемент еще раз.');
            }
            else {
                // обновим перевозчика
                DirectMSSQLQueries::updateFerryman($model);
            }

            return $this->redirect(['/ferrymen']);
        } else {
            $route = Url::to(['ferrymen/update']);

            // банковские счета
            $searchModel = new FerrymenBankDetailsSearch();
            $dpBankDetails = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $id]]);
            $dpBankDetails->pagination = false;
            $dpBankDetails->sort = [
                'defaultOrder' => ['bank_name' => SORT_ASC],
                //'attributes' => FerrymenBankDetailsSearch::sortAttributes(),
            ];

            // банковские карты
            $searchModel = new FerrymenBankCardsSearch();
            $dpBankCards = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $id]]);
            $dpBankCards->pagination = false;
            $dpBankCards->sort = [
                'defaultOrder' => ['cardholder' => SORT_ASC],
                //'attributes' => FerrymenBankCardsSearch::sortAttributes(),
            ];

            // водители
            $searchModel = new DriversSearch();
            $dpDrivers = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $id]]);
            $dpDrivers->pagination = false;
            $dpDrivers->sort = [
                'defaultOrder' => ['surname' => SORT_ASC],
                'attributes' => DriversSearch::sortAttributes(),
            ];

            // транспорт
            $searchModel = new TransportSearch();
            $dpTransport = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $id]]);
            $dpTransport->pagination = false;
            $dpTransport->sort = [
                'defaultOrder' => ['ttName' => SORT_ASC],
                'attributes' => TransportSearch::sortAttributes(),
            ];

            // файлы к объекту
            $searchModel = new FerrymenFilesSearch();
            $dpFiles = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $model->id]]);
            $dpFiles->setSort([
                'defaultOrder' => ['uploaded_at' => SORT_DESC],
            ]);
            $dpFiles->pagination = false;

            // платежные ордеры по перевозчику
            $searchModel = new PaymentOrdersSearch();
            list ($dpPaymentOrders, $poTotalAmount) = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $model->id]], $route, true);
            $dpPaymentOrders->pagination = [
                'route' => $route,
                'pageSize' => 5,
            ];

            // рейсы перевозчика
            $searchModel = new foProjectsSearch();
            list ($dpOrders, $ordersTotalAmount) = $searchModel->search([
                'route' => $route,
                $searchModel->formName() => [
                    'perevoz' => $model->name_crm,
                    'searchGroupProjectTypes' => -5,
                ]
            ], true);
            $dpOrders->pagination = [
                'route' => $route,
                'pageSize' => 10,
            ];

            // удалим лишние символы из номеров телефонов перевозчика
            $model->phone = str_replace('+7', '', $model->phone);
            if ($model->phone[0] == 7 || $model->phone[0] == '8') $model->phone = substr($model->phone, 1);

            $model->phone_dir = str_replace('+7', '', $model->phone_dir);
            if ($model->phone_dir[0] == 7 || $model->phone_dir[0] == '8') $model->phone_dir = substr($model->phone_dir, 1);

            return $this->render('update', [
                'model' => $model,
                'dpFiles' => $dpFiles,
                'dpBankDetails' => $dpBankDetails,
                'dpBankCards' => $dpBankCards,
                'dpDrivers' => $dpDrivers,
                'dpTransport' => $dpTransport,
                'dpPaymentOrders' => $dpPaymentOrders,
                'poTotalAmount' => $poTotalAmount,
                'dpOrders' => $dpOrders,
                'ordersTotalAmount' => $ordersTotalAmount,
            ]);
        }
    }

    /**
     * Deletes an existing Ferrymen model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/ferrymen']);
    }

    /**
     * Finds the Ferrymen model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ferrymen the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ferrymen::findOne($id)) !== null) {
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
        $upload_path = FerrymenFiles::getUploadsFilepath();
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
                    $fu = new FerrymenFiles();
                    $fu->ferryman_id = $obj_id;
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
        $model = FerrymenFiles::findOne(['id' => $id]);
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
            $model = FerrymenFiles::findOne($id);
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
        $model = FerrymenFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return Html::img(Yii::getAlias('@uploads-ferrymen') . '/' . $model->fn, ['width' => 600]);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/ferrymen/download-from-outside', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
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
        $model = FerrymenFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->ferryman_id;
            $model->delete();

            return $this->redirect(['/ferrymen/update', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Добавляет банковский счет.
     * @throws NotFoundHttpException если перевозчик не будет обнаружен
     * @throws BadRequestHttpException если перевозчик не передается в параметрах
     */
    public function actionCreateBankAccount()
    {
        $model = new FerrymenBankDetails();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save())
                return $this->redirect(['/ferrymen/update', 'id' => $model->ferryman_id]);
            else
                return $this->render('/ferrymen-bank-details/create', ['model' => $model]);
        }

        $ferryman_id = Yii::$app->request->post('ferryman_id');
        if ($ferryman_id != null) {
            $ferryman = Ferrymen::findOne($ferryman_id);
            if ($ferryman != null) {
                $model->ferryman_id = $ferryman_id;

                return $this->render('/ferrymen-bank-details/create', ['model' => $model]);
            } else {
                throw new NotFoundHttpException('Запрошенная страница не существует.');
            }
        } else {
            throw new BadRequestHttpException('Обязательные параметры не заданы.');
        }
    }

    /**
     * Удаляет банковский счет.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если счет не будет обнаружен
     */
    public function actionDeleteBankAccount($id)
    {
        $driver = FerrymenBankDetails::findOne($id);
        if ($driver != null) {
            $ferryman_id = $driver->ferryman_id;
            $driver->delete();

            return $this->redirect(['/ferrymen/update', 'id' => $ferryman_id]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Добавляет банковскую карту.
     * @throws NotFoundHttpException если перевозчик не будет обнаружен
     * @throws BadRequestHttpException если перевозчик не передается в параметрах
     */
    public function actionCreateBankCard()
    {
        $model = new FerrymenBankCards();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save())
                return $this->redirect(['/ferrymen/update', 'id' => $model->ferryman_id]);
            else
                return $this->render('/ferrymen-bank-cards/create', ['model' => $model]);
        }

        $ferryman_id = Yii::$app->request->post('ferryman_id');
        if ($ferryman_id != null) {
            $ferryman = Ferrymen::findOne($ferryman_id);
            if ($ferryman != null) {
                $model->ferryman_id = $ferryman_id;

                return $this->render('/ferrymen-bank-cards/create', ['model' => $model]);
            } else {
                throw new NotFoundHttpException('Запрошенная страница не существует.');
            }
        } else {
            throw new BadRequestHttpException('Обязательные параметры не заданы.');
        }
    }

    /**
     * Удаляет банковскую карту.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если карта не будет обнаружена
     */
    public function actionDeleteBankCard($id)
    {
        $driver = FerrymenBankCards::findOne($id);
        if ($driver != null) {
            $ferryman_id = $driver->ferryman_id;
            $driver->delete();

            return $this->redirect(['/ferrymen/update', 'id' => $ferryman_id]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Добавляет водителя.
     * @throws NotFoundHttpException если перевозчик не будет обнаружен
     * @throws BadRequestHttpException если перевозчик не передается в параметрах
     */
    public function actionCreateDriver()
    {
        $model = new Drivers();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save())
                return $this->redirect(['/ferrymen/update', 'id' => $model->ferryman_id]);
            else
                return $this->render('/drivers/create', ['model' => $model]);
        }

        $ferryman_id = Yii::$app->request->post('ferryman_id');
        if ($ferryman_id != null) {
            $ferryman = Ferrymen::findOne($ferryman_id);
            if ($ferryman != null) {
                $model->ferryman_id = $ferryman_id;

                return $this->render('/drivers/create', ['model' => $model]);
            } else {
                throw new NotFoundHttpException('Запрошенная страница не существует.');
            }
        } else {
            throw new BadRequestHttpException('Обязательные параметры не заданы.');
        }
    }

    /**
     * Удаляет водителя.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если водитель не будет обнаружен
     */
    public function actionDeleteDriver($id)
    {
        $driver = Drivers::findOne($id);
        if ($driver != null) {
            $ferryman_id = $driver->ferryman_id;
            if (!Yii::$app->user->can('root')) {
                // для пользователей с ограниченными правами только лишь помечаем на удаление
                $driver->is_deleted = true;
                $driver->save(false);
            }
            else $driver->delete();

            return $this->redirect(['/ferrymen/update', 'id' => $ferryman_id]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Добавляет транспортное средство.
     * @throws NotFoundHttpException если перевозчик не будет обнаружен
     * @throws BadRequestHttpException если перевозчик не передается в параметрах
     */
    public function actionCreateTransport()
    {
        $model = new Transport();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save())
                return $this->redirect(['/ferrymen/update', 'id' => $model->ferryman_id]);
            else
                return $this->render('/transport/create', ['model' => $model]);
        }

        $ferryman_id = Yii::$app->request->post('ferryman_id');
        if ($ferryman_id != null) {
            $ferryman = Ferrymen::findOne($ferryman_id);
            if ($ferryman != null) {
                $model->ferryman_id = $ferryman_id;

                return $this->render('/transport/create', ['model' => $model]);
            } else {
                throw new NotFoundHttpException('Запрошенная страница не существует.');
            }
        } else {
            throw new BadRequestHttpException('Обязательные параметры не заданы.');
        }
    }

    /**
     * Удаляет транспортное средство.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если водитель не будет обнаружен
     */
    public function actionDeleteTransport($id)
    {
        $transport = Transport::findOne($id);
        if ($transport != null) {
            $ferryman_id = $transport->ferryman_id;
            if (!Yii::$app->user->can('root')) {
                // для пользователей с ограниченными правами только лишь помечаем на удаление
                $transport->is_deleted = true;
                $transport->save(false);
            }
            else $transport->delete();

            return $this->redirect(['/ferrymen/update', 'id' => $ferryman_id]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Отображает список инструктажей водителя.
     * @param $id integer идентификатор водителя
     * @return mixed
     * @throws NotFoundHttpException если водитель не будет обнаружен
     */
    public function actionDriversInstructings($id)
    {
        $driver = Drivers::findOne($id);
        if ($driver != null) {
            $ferryman = Ferrymen::findOne($driver->ferryman_id);

            $searchModel = new DriversInstructingsSearch();
            $dataProvider = $searchModel->search([$searchModel->formName() => ['driver_id' => $id]]);
            $dataProvider->sort = [
                'defaultOrder' => ['instructed_at' => SORT_DESC],
            ];

            return $this->render('/drivers-instructings/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'driver' => $driver,
                'ferryman' => $ferryman,
            ]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Creates a new DriversInstructings model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws NotFoundHttpException если инструктаж не будет обнаружен
     */
    public function actionCreateInstructing()
    {
        $model = new DriversInstructings();
        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['/ferrymen/drivers-instructings', 'id' => $model->driver_id]);

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Deletes an existing DriversInstructings model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если инструктаж не будет обнаружен
     */
    public function actionDeleteInstructing($id)
    {
        $di = DriversInstructings::findOne($id);
        if ($di != null) {
            $driver_id = $di->driver_id;
            $di->delete();

            return $this->redirect(['/ferrymen/drivers-instructings', 'id' => $driver_id]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Отображает список техосмотров транспортного средства.
     * @param $id integer идентификатор автомобиля
     * @return mixed
     * @throws NotFoundHttpException если автомобиль не будет обнаружен
     */
    public function actionTransportsInspections($id)
    {
        $transport = Transport::findOne($id);
        if ($transport != null) {
            $searchModel = new TransportInspectionsSearch();
            $dataProvider = $searchModel->search([$searchModel->formName() => ['transport_id' => $id]]);

            return $this->render('/transports-inspections/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'transport' => $transport,
                'ferryman' => Ferrymen::findOne($transport->ferryman_id),
            ]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Creates a new TransportInspections model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws NotFoundHttpException если техосмотр не будет обнаружен
     */
    public function actionCreateInspection()
    {
        $model = new TransportInspections();
        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['/ferrymen/transports-inspections', 'id' => $model->transport_id]);

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Deletes an existing TransportInspections model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если техосмотр не будет обнаружен
     */
    public function actionDeleteInspection($id)
    {
        $ti = TransportInspections::findOne($id);
        if ($ti != null) {
            $transport_id = $ti->transport_id;
            $ti->delete();

            return $this->redirect(['/ferrymen/transports-inspections', 'id' => $transport_id]);
        }
        else
            throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Функция выполняет поиск перевозчика по наименованию, переданному в параметрах.
     * Для виджетов Typeahead.
     * @param $q string
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionListOfFerrymenForTypeahead($q)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $query = Ferrymen::find()->select([
                'id',
                'value' => 'name',
                'description' => 'CONCAT(ferrymanDrivers.details, " ", ferrymanTransport.details)',
                'drivers' => 'ferrymanDrivers.details',
                'transport' => 'ferrymanTransport.details',
            ])->leftJoin('(
            SELECT
                drivers.ferryman_id,
                COUNT(drivers.id) AS count,
                GROUP_CONCAT(CONCAT(drivers.surname, " ", drivers.name, " ", drivers.patronymic) SEPARATOR ", ") AS details
            FROM drivers
            GROUP BY drivers.ferryman_id
        ) AS ferrymanDrivers', '`ferrymen`.`id` = `ferrymanDrivers`.`ferryman_id`')
            ->leftJoin('(
            SELECT
                transport.ferryman_id,
                COUNT(transport.id) AS count,
                GROUP_CONCAT(CONCAT(transport.vin, " ", transport.rn, CASE WHEN (transport.trailer_rn IS NULL OR transport.trailer_rn="") THEN "" ELSE CONCAT(" прицеп ", transport.trailer_rn) END) SEPARATOR ", ") AS details
            FROM transport
            GROUP BY transport.ferryman_id
        ) AS ferrymanTransport', '`ferrymen`.`id` = `ferrymanTransport`.`ferryman_id`')
            ->andFilterWhere([
                'or',
                ['like', 'name', $q],
                ['like', 'ferrymanDrivers.details', $q],
                ['like', 'ferrymanTransport.details', $q],
            ])->orderBy('name');

            return $query->asArray()->all();
        }
    }

    /**
     * @param $ati_code
     * @return bool
     */
    public function actionValidateAtiCode($ati_code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $client = new Client(['baseUrl' => 'http://api.ati.su/v1.2/summary/']);
        $response = $client->get($ati_code)->send();
        if ($response->isOk)
            return true;
        else
            return $response->data;
    }

    /**
     * Рендерит форму приглашения перевозчика создать аккаунт в личном кабинете.
     * @param $id integer идентификатор перевозчика, который приглашается
     * @return mixed
     */
    public function actionInviteFerrymanForm($id)
    {
        if (Yii::$app->request->isAjax) {
            $id = intval($id);
            $ferryman = Ferrymen::findOne($id);
            if ($ferryman != null) {
                $invitationLast = FerrymenInvitations::find()->where(['ferryman_id' => $id])->orderBy('created_at DESC')->one();
                $email = '';
                if ($ferryman->email_dir != null && trim($ferryman->email_dir) != '') $email = $ferryman->email_dir;
                if ($email == '' && $ferryman->email != null && trim($ferryman->email) != '') $email = $ferryman->email;

                $invitationForm = new FerrymanInvitationForm([
                    'ferryman_id' => $id,
                    'email' => $email,
                ]);

                return $this->renderAjax('_invite_form', [
                    'model' => $invitationForm,
                    'invitationLast' => $invitationLast,
                ]);
            }
        }

        return false;
    }

    /**
     * AJAX-валидация формы отправки приглашения.
     */
    public function actionValidateInvitation()
    {
        $model = new FerrymanInvitationForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            echo json_encode(\yii\widgets\ActiveForm::validate($model));
            Yii::$app->end();
        }
    }

    /**
     * Отправляет приглашение перевозчику создать аккаунт в личном кабинете.
     * @return array|bool
     */
    public function actionSendInvitation()
    {
        $model = new FerrymanInvitationForm();

        if ($model->load(Yii::$app->request->post())) {
            return $model->sendInvitation();
        }
    }
}
