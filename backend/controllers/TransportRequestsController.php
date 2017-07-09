<?php

namespace backend\controllers;

use Yii;
use common\models\TransportRequests;
use common\models\TransportRequestsSearch;
use common\models\TransportRequestsFiles;
use common\models\TransportRequestsFilesSearch;
use common\models\TransportRequestsStates;
use common\models\TransportRequestsTransport;
use common\models\TransportRequestsWaste;
use common\models\Fkko;
use common\models\PackingTypes;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TransportRequestsController implements the CRUD actions for TransportRequests model.
 */
class TransportRequestsController extends Controller
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
                    'index', 'create', 'update', 'delete', 'list-of-fkko-for-typeahead', 'list-of-packing-types-for-typeahead',
                    'render-fkko-row', 'delete-fkko-row', 'render-transport-row', 'delete-transport-row',
                    'similar-statements', 'compose-region-fields',
                    'upload-files', 'download-file', 'delete-file',
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root', 'logist', 'sales_department_manager'],
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
     * Делает выборку файлов, приаттаченных к запросу на транспорт
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchFiles()
    {
        $searchModel = new TransportRequestsFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['tr_id' => $this->id]]);
        $dpFiles->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dpFiles->pagination = false;

        return $dpFiles;
    }

    /**
     * Lists all TransportRequests models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransportRequestsSearch();

        // для менеджеров отбор только собственных объектов (где он является автором)
        $conditions = [];
        if (Yii::$app->user->can('sales_department_manager')) {
            $conditions = [
                $searchModel->formName() => [
                    'created_by' => Yii::$app->user->id,
                ],
            ];
        }

        $dataProvider = $searchModel->search(ArrayHelper::merge(
            $conditions,
            Yii::$app->request->queryParams
        ));

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new TransportRequests model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TransportRequests();
        $model->state_id = TransportRequestsStates::STATE_НОВЫЙ;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/transport-requests', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TransportRequests model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException если доступа к запрошенному объекту нет
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // проверим наличие доступа у текущего пользователя к договору
        // если, конечно, это не пользователь с полными правами или логист
        if (Yii::$app->user->can('sales_department_manager')) {
            if ($model->created_by != Yii::$app->user->id) {
                return $this->render('/common/forbidden_foreign', [
                    'details' => [
                        'breadcrumbs' => ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
                        'modelRep' => $model->representation,
                        'buttonCaption' => 'Запросы на транспорт',
                        'buttonUrl' => ['/transport-requests'],
                    ],
                ]);
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            // пришедшие снаружи идентификаторы переводим в модели строк табличной части "Отходы"
            $postWaste = $model->makeWasteModelsFromPostArray();

            // формируем массив моделей табличной части "Транспорт"
            $postTransport = $model->makeTransportModelsFromPostArray();

            // если изменился статус на "Закрыто", то зафиксируем время
            if ($model->closeRequest)
                if (isset($model->oldAttributes['state_id']))
                    if ($model->oldAttributes['state_id'] != TransportRequestsStates::STATE_ЗАКРЫТ) {
                        $model->state_id = TransportRequestsStates::STATE_ЗАКРЫТ;
                        $model->finished_at = time();
                    }

            if ($model->validate() && $model->save(false)) {
                // записываем заново табличную часть "Отходы"
                $successWaste = true;
                TransportRequestsWaste::deleteAll(['tr_id' => $model->id]);

                foreach ($postWaste as $tp) {
                    $row = new TransportRequestsWaste();
                    $row->attributes = $tp->attributes;
                    if (!$row->save()) {
                        $successWaste = false;
                        $details = '';
                        foreach ($row->errors as $error)
                            foreach ($error as $detail)
                                $details .= '<p>'.$detail.'</p>';

                        Yii::$app->getSession()->setFlash('error', $details);
                        break;
                    }
                }

                // записываем заново табличную часть "Транспорт"
                $successTransport = true;
                TransportRequestsTransport::deleteAll(['tr_id' => $model->id]);

                foreach ($postTransport as $tp) {
                    $row = new TransportRequestsTransport();
                    $row->attributes = $tp->attributes;
                    if (!$row->save()) {
                        $successTransport = false;
                        $details = '';
                        foreach ($row->errors as $error)
                            foreach ($error as $detail)
                                $details .= '<p>'.$detail.'</p>';

                        Yii::$app->getSession()->setFlash('error', $details);
                        break;
                    }
                }

                if ($successWaste && $successTransport) return $this->redirect(['/transport-requests']);
            }

            return $this->render('update', [
                'model' => $model,
                'waste' => $postWaste,
                'transport' => $postTransport,
                'dpFiles' => $this->fetchFiles(),
            ]);
        } else {
            // при открытии запроса логистом запрос ставится в статус "В обработке"
            if (Yii::$app->user->can('logist') && $model->state_id == TransportRequestsStates::STATE_НОВЫЙ) {
                $model->state_id = TransportRequestsStates::STATE_ОБРАБАТЫВАЕТСЯ;
                $model->save(false);
            }

            // табличная часть с отходами
            $waste = TransportRequestsWaste::find()->where(['tr_id' => $model->id])->all();

            // табличная часть с транспортом
            $transport = TransportRequestsTransport::find()->where(['tr_id' => $model->id])->all();

            return $this->render('update', [
                'model' => $model,
                'waste' => $waste,
                'transport' => $transport,
                'dpFiles' => $this->fetchFiles(),
            ]);
        }
    }

    /**
     * Deletes an existing TransportRequests model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/transport-requests']);
    }

    /**
     * Finds the TransportRequests model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransportRequests the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TransportRequests::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Функция выполняет поиск отходов по коду ФККО и наименованию от значения переданного в параметрах.
     * Для виджетов Typeahead.
     * @param $q string
     * @param $counter integer|null
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionListOfFkkoForTypeahead($q, $counter = null)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $query = Fkko::find()->select([
                'id',
                'value' => 'CONCAT(fkko_code, " - ", fkko_name)',
                $counter . ' AS `counter`',
            ])->andFilterWhere([
                'or',
                ['like', 'fkko_code', $q],
                ['like', 'fkko_name', $q],
            ]);

            return $query->asArray()->all();
        }
    }

    /**
     * Функция выполняет поиск видов упаковки по наименованию, переданному в параметрах.
     * Для виджетов Typeahead.
     * @param $q string
     * @param $counter integer|null
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionListOfPackingTypesForTypeahead($q, $counter = null)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $query = PackingTypes::find()->select([
                'id',
                'value' => 'name',
                $counter . ' AS `counter`',
            ])
                ->andFilterWhere(['like', 'name', $q]);

            return $query->asArray()->all();
        }
    }

    /**
     * Формирует и отдает форму с похожими движениями.
     * @param $request_id integer запрос на транспорт
     * @param $ca_id integer идентификатор контрагента
     * @return mixed
     */
    public function actionSimilarStatements($request_id, $ca_id)
    {
        $ca_id = intval($ca_id);
        $request_id = intval($request_id);
        if ($request_id > 0 && $ca_id > 0) {
            $query = TransportRequests::find()
                ->select([
                    '*',
                    'id' => 'transport_requests.id',
                    'tpWasteLinear' => '(
	                    SELECT GROUP_CONCAT(CONCAT(transport_requests_waste.fkko_name, " (", FORMAT(transport_requests_waste.measure, 0, "ru_RU"), " ", units.name, ")") SEPARATOR "\n") FROM transport_requests_waste
	                    INNER JOIN units ON units.id = transport_requests_waste.unit_id
	                    WHERE transport_requests_waste.tr_id = transport_requests.id
                    )',
                    'tpTransportLinear' => '(
	                    SELECT GROUP_CONCAT(CONCAT(transport_types.name, " ", FORMAT(transport_requests_transport.amount, 0, "ru_RU"), " р.") SEPARATOR ", ") FROM transport_types
	                    INNER JOIN transport_requests_transport ON transport_requests_transport.tt_id = transport_types.id
	                    WHERE transport_requests_transport.tr_id = transport_requests.id
                    )',
                ])
                ->where(['customer_id' => $ca_id])
                ->andWhere(['state_id' => TransportRequestsStates::STATE_ЗАКРЫТ])
                ->andWhere('transport_requests.id <> ' . $request_id) // кроме текущего запроса
                ->orderBy('created_at DESC');

            $requests = new ActiveDataProvider([
                'query' => $query,
                'pagination' => false,
                'sort' => false,
            ]);

            return $this->renderAjax('_similar_statements', [
                'dataProvider' => $requests,
            ]);
        }
    }

    /**
     * Возвращает результат рендера строки табличной части.
     * @param $counter integer
     * @return string
     */
    public function actionRenderFkkoRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            $model = new TransportRequestsWaste();
            $tr = new TransportRequests();

            return $this->renderAjax('_row_fkko', [
                'tr' => $tr,
                'model' => $model,
                'counter' => $counter + 1,
            ]);
        }
    }

    /**
     * Выполняет удаление строки табличной части из документа.
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteFkkoRow($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($id != null) {
            TransportRequestsWaste::deleteAll(['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Возвращает результат рендера строки табличной части.
     * @param $counter integer
     * @return string
     */
    public function actionRenderTransportRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            $model = new TransportRequestsTransport();
            $tr = new TransportRequests();

            return $this->renderAjax('_row_transport', [
                'tr' => $tr,
                'model' => $model,
                'counter' => $counter + 1,
            ]);
        }
    }

    /**
     * Выполняет удаление строки табличной части из документа.
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteTransportRow($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($id != null) {
            TransportRequestsTransport::deleteAll(['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Формирует поле Город для выбранного пользователем региона.
     * @param $region_id integer идентификатор региона
     * @return mixed
     */
    public function actionComposeRegionFields($region_id)
    {
        if (Yii::$app->request->isAjax) {
            $region_id = intval($region_id);
            if ($region_id > 0) {
                $model = new TransportRequests();
                $model->region_id = $region_id;

                return $this->renderAjax('_city_field', [
                    'model' => $model,
                    'form' => \yii\bootstrap\ActiveForm::begin(),
                ]);
            }
        }

        return '';
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = TransportRequestsFiles::getUploadsFilepath();
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
                    $fu = new TransportRequestsFiles();
                    $fu->tr_id = $obj_id;
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
            $model = TransportRequestsFiles::findOne($id);
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
        $model = TransportRequestsFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->tr_id;
            $model->delete();

            return $this->redirect(['/transport-requests/update', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }
}