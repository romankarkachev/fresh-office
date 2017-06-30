<?php

namespace backend\controllers;

use Yii;
use common\models\Ferrymen;
use common\models\FerrymenSearch;
use common\models\Drivers;
use common\models\DriversSearch;
use common\models\Transport;
use common\models\TransportSearch;
use common\models\DriversInstructings;
use common\models\DriversInstructingsSearch;
use common\models\TransportInspections;
use common\models\TransportInspectionsSearch;
use common\models\DirectMSSQLQueries;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
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
                'only' => [
                    'index', 'create', 'update', 'delete',
                    'create-driver', 'delete-driver', 'create-transport', 'delete-transport',
                    'drivers-instructings', 'create-instructing', 'delete-instructing',
                    'transports-inspections', 'create-inspection', 'delete-inspection',
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root', 'logist'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'create-driver' => ['POST'],
                    'delete-driver' => ['POST'],
                    'create-transport' => ['POST'],
                    'delete-transport' => ['POST'],
                    'create-instructing' => ['POST'],
                    'delete-instructing' => ['POST'],
                    'create-inspection' => ['POST'],
                    'delete-inspection' => ['POST'],
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
            return $this->render('create', [
                'model' => $model,
            ]);
        }
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
            // водители
            $searchModel = new DriversSearch();
            $dpDrivers = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $id]]);
            $dpDrivers->pagination = false;
            $dpDrivers->sort = [
                'defaultOrder' => ['surname' => SORT_ASC],
                'attributes' => [
                    'id',
                    'surname',
                    'name',
                    'patronymic',
                    'driver_license',
                    'phone',
                ],
            ];

            // транспорт
            $searchModel = new TransportSearch();
            $dpTransport = $searchModel->search([$searchModel->formName() => ['ferryman_id' => $id]]);
            $dpTransport->pagination = false;
            $dpTransport->sort = [
                'defaultOrder' => ['vin' => SORT_ASC],
                'attributes' => [
                    'id',
                    'vin',
                    'ferryman_id',
                    'tt_id',
                    'tc_id',
                    'rn',
                    'trailer_rn',
                    'comment',
                    'ttName' => [
                        'asc' => ['transport_types.name' => SORT_ASC],
                        'desc' => ['transport_types.name' => SORT_DESC],
                    ],
                    'tcName' => [
                        'asc' => ['technical_conditions.name' => SORT_ASC],
                        'desc' => ['technical_conditions.name' => SORT_DESC],
                    ],
                ],
            ];

            return $this->render('update', [
                'model' => $model,
                'dpDrivers' => $dpDrivers,
                'dpTransport' => $dpTransport,
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
            $driver->delete();

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
            $transport->delete();

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
}
