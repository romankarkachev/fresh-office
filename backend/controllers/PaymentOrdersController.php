<?php

namespace backend\controllers;

use Yii;
use common\models\PaymentOrders;
use common\models\PaymentOrdersSearch;
use backend\models\PaymentOrdersImport;
use common\models\Ferrymen;
use common\models\FerrymenBankCards;
use common\models\FerrymenBankDetails;
use common\models\PaymentOrdersFiles;
use common\models\PaymentOrdersFilesSearch;
use common\models\PaymentOrdersStates;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use moonland\phpexcel\Excel;

/**
 * PaymentOrdersController implements the CRUD actions for PaymentOrders model.
 */
class PaymentOrdersController extends Controller
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
                        'actions' => [
                            'index', 'compose-pd-field', 'change-state-on-the-fly', 'upload-files', 'preview-file',
                            'delete-file'
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'accountant'],
                    ],
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'accountant'],
                    ],
                    [
                        'actions' => ['import', 'delete', 'drop-drafts'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'drop-drafts' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PaymentOrders models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentOrdersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new PaymentOrders model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PaymentOrders();

        if ($model->load(Yii::$app->request->post())) {
            // создается всегда в статусе черновика
            $model->creation_type = PaymentOrders::PAYMENT_ORDER_CREATION_TYPE_ВРУЧНУЮ;
            $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК;

            if ($model->save()) return $this->redirect(['/payment-orders/update', 'id' => $model->id]);
        } else {
            $key = 'ids_for_payment_order_' . Yii::$app->user->id;
            $ids = Yii::$app->session->get($key);
            if ($ids != null) {
                $model->projects = implode(',', $ids);
                $model->calculateProjectsTotalAmount();
                Yii::$app->session->remove($key);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PaymentOrders model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $state = $model->state_id;

        if ($model->load(Yii::$app->request->post())) {
            if (trim($model->comment) == '') $model->comment = null;

            if ($model->validate()) {
                // если нажата кнопка "Отправить на согласование"
                if (Yii::$app->request->post('order_ready') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ;

                // если нажата кнопка "Согласовать"
                elseif (Yii::$app->request->post('order_approve') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН;

                // если нажата кнопка "Отказать"
                elseif (Yii::$app->request->post('order_reject') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ;

                // если нажата кнопка "Подать повторно"
                elseif (Yii::$app->request->post('order_repeat') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК;

                // если нажата кнопка "Оплачено"
                elseif (Yii::$app->request->post('order_paid') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН;

                if ($model->save()) return $this->redirect(['/payment-orders']); else $model->state_id = $state; // возвращаем статус
            }
            else $model->state_id = $state; // возвращаем статус
        }

        // файлы к объекту
        $searchModel = new PaymentOrdersFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['po_id' => $model->id]]);
        $dpFiles->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dpFiles->pagination = false;

        $formName = 'view';
        if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК) $formName = 'update';

        return $this->render($formName, [
            'model' => $model,
            'dpFiles' => $dpFiles,
        ]);
    }

    /**
     * Deletes an existing PaymentOrders model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/payment-orders']);
    }

    /**
     * Finds the PaymentOrders model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PaymentOrders the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaymentOrders::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Формирует поле для выборка банковского счета/карты перевозчика.
     * @param $ferryman_id integer идентификатор перевозчика
     * @param $pd integer способ расчетов с перевозчиком
     * @return mixed
     */
    public function actionComposePdField($ferryman_id, $pd)
    {
        if (Yii::$app->request->isAjax) {
            $ferryman_id = intval($ferryman_id);
            if ($ferryman_id > 0)
                $ferryman = Ferrymen::findOne($ferryman_id);
                if ($ferryman != null) {
                    switch ($pd) {
                        case PaymentOrders::PAYMENT_DESTINATION_ACCOUNT:
                            $dataSet = FerrymenBankDetails::arrayMapForSelect2($ferryman_id);
                            break;
                        case PaymentOrders::PAYMENT_DESTINATION_CARD:
                            $dataSet = FerrymenBankCards::arrayMapForSelect2($ferryman_id);
                            break;
                    }
                    if (isset($dataSet)) {
                        return $this->renderAjax('_block_pd', [
                            'model' => new PaymentOrders(),
                            'form' => \yii\bootstrap\ActiveForm::begin(),
                            'dataSet' => $dataSet,
                        ]);
                    }
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
        $upload_path = PaymentOrdersFiles::getUploadsFilepath();
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
                    $fu = new PaymentOrdersFiles();
                    $fu->po_id = $obj_id;
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
            $model = PaymentOrdersFiles::findOne($id);
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
        $model = PaymentOrdersFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return \yii\helpers\Html::img(Yii::getAlias('@uploads-payment-orders') . '/' . $model->fn, ['width' => '100%']);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/payment-orders/download-file', 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
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
        $model = PaymentOrdersFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->po_id;
            $model->delete();

            return $this->redirect(['/payment-orders/update', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Меняет статус ордера по-быстрому, на лету.
     * @param $po_id integer идентфикатор платежного ордера
     * @param $state_id integer новый статус, который необходимо присвоить
     * @return bool
     */
    public function actionChangeStateOnTheFly($po_id, $state_id)
    {
        $state_id = intval($state_id);
        $state = PaymentOrdersStates::findOne($state_id);

        $po_id = intval($po_id);
        $model = PaymentOrders::findOne($po_id);
        if ($state != null && $model != null) {
            $model->state_id = $state_id;
            return $model->save(false);
        }

        return false;
    }

    /**
     * Отображает страницу импорта платежных ордеров из файла Excel и выполняет его.
     * @return mixed
     */
    public function actionImport()
    {
        $model = new PaymentOrdersImport();

        if (Yii::$app->request->isPost) {
            $model->importFile = UploadedFile::getInstance($model, 'importFile');
            $filename = Yii::getAlias('@uploads').'/'.Yii::$app->security->generateRandomString().'.'.$model->importFile->extension;
            if ($model->upload($filename)) {
                $model->load(Yii::$app->request->post());
                // если файл удалось успешно загрузить на сервер
                // выбираем все данные из файла в массив
                $data = Excel::import($filename, [
                    'setFirstRecordAsKeys' => false,
                ]);
                if (count($data) > 0) {
                    // если удалось прочитать, сразу удаляем файл
                    unlink($filename);

                    // берем массив в отдельную переменную, чтобы обработать его и получить в результате
                    // только идентификаторы проектов, которые присутствуют в файле
                    $project_ids = $data;
                    // если первая строка - заголовок таблицы, удалим ее
                    if (!is_numeric($project_ids[1]['C'])) unset($project_ids[1]);
                    // берем только колонку id, раскладываем массив в строку, из строки убираем в конце запятую
                    //$project_ids = trim(implode(',', ArrayHelper::getColumn($project_ids, 'C')), ',');
                    //$projects = DirectMSSQLQueries::fetchProjectsForDatePayment($project_ids);

                    // перебираем массив и создаем новые элементы
                    $success_records_count = 0; // массив успешно созданных записей
                    $errors_import = []; // массив для ошибок при импорте
                    $row_number = 1; // 0-я строка - это заголовок

                    $prevFeryman = '';
                    $poCost = 0;
                    $poCas = [];
                    $poProjects = [];
                    foreach ($data as $row) {
                        // проверяем обязательные поля, если первое поле не заполнено, останавливаем процесс
                        if (trim($row['C']) == '') break;

                        // это может быть еще заголовок таблицы, пропускаем его
                        if (!is_numeric(trim($row['C']))) continue;

                        // перевозчик в текущей строке
                        $ferryman = trim($row['M']);

                        if ($ferryman != $prevFeryman && $prevFeryman != '') {
                            $ferrymanModel = Ferrymen::findOne(['name_crm' => $prevFeryman]);
                            if ($ferrymanModel != null) {
                                $paymentOrder = new PaymentOrders([
                                    'creation_type' => PaymentOrders::PAYMENT_ORDER_CREATION_TYPE_ИМПОРТ_ИЗ_EXCEL,
                                    'state_id' => PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК,
                                    'ferryman_id' => $ferrymanModel->id,
                                    'projects' => implode(',', $poProjects),
                                    'cas' => implode(', ', $poCas),
                                    'amount' => $poCost,
                                    'comment' => 'Импорт из Excel',
                                ]);
                                if (!$paymentOrder->save()) {
                                    $details = '';
                                    foreach ($paymentOrder->errors as $error)
                                        foreach ($error as $detail)
                                            $details .= '<p>' . $detail . '</p>';
                                    $errors_import[] = 'Не удалось создать платежный ордер на перевозчика ' . $prevFeryman . '. Ошибки: ' . $details;
                                }
                            }
                            else {
                                $errors_import[] = 'Перевозчик ' . $prevFeryman . ' не был обнаружен. Платежный ордер не создан.';
                            }

                            $poCost = 0;
                            $poCas = [];
                            $poProjects = [];
                        }

                        // накапливаем проекты
                        $poProjects[] = trim($row['C']);

                        // накапливаем контрагентов (клиентов)
                        $ca = trim($row['G']);
                        if (!in_array($ca, $poCas)) $poCas[] = $ca;

                        // приведем в человеческий вид сумму
                        $amount = trim(trim($row['I']));
                        $amount = preg_replace("/[^0-9\.]/", '', $amount);
                        $amount = floatval($amount);
                        $poCost += $amount;

                        $prevFeryman = $ferryman;
                        $row_number++;
                    }; // foreach

                    // после завершения цикла еще раз процедуру сохранения ордера для последнего перевозчика
                    $ferrymanModel = Ferrymen::findOne(['name_crm' => $prevFeryman]);
                    if ($ferrymanModel != null) {
                        $paymentOrder = new PaymentOrders([
                            'creation_type' => PaymentOrders::PAYMENT_ORDER_CREATION_TYPE_ИМПОРТ_ИЗ_EXCEL,
                            'state_id' => PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК,
                            'ferryman_id' => $ferrymanModel->id,
                            'projects' => implode(',', $poProjects),
                            'cas' => implode(', ', $poCas),
                            'amount' => $poCost,
                            'comment' => 'Импорт из Excel',
                        ]);
                        if (!$paymentOrder->save()) {
                            $details = '';
                            foreach ($paymentOrder->errors as $error)
                                foreach ($error as $detail)
                                    $details .= '<p>' . $detail . '</p>';
                            $errors_import[] = 'Не удалось создать платежный ордер на перевозчика ' . $prevFeryman . '. Ошибки: ' . $details;
                        }
                    }
                    else {
                        $errors_import[] = 'Перевозчик ' . $prevFeryman . ' не был обнаружен. Платежный ордер не создан.';
                    }

                    // зафиксируем ошибки, чтобы показать
                    if (count($errors_import) > 0) {
                        $errors = '';
                        foreach ($errors_import as $error)
                            $errors .= '<p>'.$error.'</p>';
                        Yii::$app->getSession()->setFlash('error', $errors);
                    } else {
                        $addition = '';
                        if ($success_records_count > 0)
                            $addition = ' Обновлено записей: ' . $success_records_count . '.';
                        Yii::$app->getSession()->setFlash('success', 'Импорт завершен.' . $addition);
                    }

                }; // count > 0

                //return $this->redirect(['freights-payments']);
            }
        };

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    /**
     * Удаляет все черновики.
     * @return mixed
     */
    public function actionDropDrafts()
    {
        Url::remember(Yii::$app->request->referrer);
        $records = PaymentOrders::find()->where(['state_id' => PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК])->all();
        foreach ($records as $record) $record->delete();
        return $this->goBack();
    }
}
