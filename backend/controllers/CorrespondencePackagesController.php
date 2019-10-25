<?php

namespace backend\controllers;

use Yii;
use common\models\CorrespondencePackages;
use common\models\CorrespondencePackagesSearch;
use common\models\CorrespondencePackagesFiles;
use common\models\CorrespondencePackagesFilesSearch;
use common\models\CorrespondencePackagesStates;
use common\models\CorrespondencePackagesHistorySearch;
use common\models\CounteragentsPostAddresses;
use common\models\foListEmailClient;
use common\models\PrintEnvelopeForm;
use common\models\PostDeliveryKinds;
use common\models\ProjectsStates;
use common\models\ComposePackageForm;
use common\models\PadKinds;
use common\models\DirectMSSQLQueries;
use common\models\Profile;
use common\models\CpBlContactEmails;
use kartik\mpdf\Pdf;
use yii\helpers\ArrayHelper;
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
                        'actions' => ['delete', 'temp'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update',
                            'compose-package-by-selection', 'compose-package', 'compose-envelope',
                            'create-address-form', 'counteragent-casting-by-name',
                            'fetch-contact-persons', 'fetch-contact-emails', 'normalize-empty-addresses',
                            'upload-files', 'preview-file', 'delete-file',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'operator_head', 'sales_department_manager', 'dpc_head', 'ecologist', 'ecologist_head'],
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
     * Делает выборку файлов, приаттаченных пользователями системы к пакету корреспонденции.
     * @param $parent_id integer идентификатор родительского объекта, файлы которого выбираются
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchFiles($parent_id)
    {
        $searchModel = new CorrespondencePackagesFilesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['cp_id' => $parent_id]]);
        $dataProvider->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Рендерит список файлов, прикрепленных к пакету корреспонденции.
     * @param integer $parent_id идентификатор родительского объекта, приаттаченные файлы которого рендерятся
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderFiles($parent_id)
    {
        return $this->renderAjax('_files', [
            'dataProvider' => $this->fetchFiles($parent_id),
        ]);
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
            $model->is_manual = true; // всегда созданные роботом отмечаются противоположным признаком
            $model->state_id = ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ;
            $model->cps_id = CorrespondencePackagesStates::STATE_ЧЕРНОВИК;
            $model->pad = $model->convertPadTableToArray();
            $model->scenario = 'manual_creating';

            if ($model->save()) return $this->redirect(['/correspondence-packages/update', 'id' => $model->id]);
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
        $cps_id = $model->cps_id;

        if ($model->load(Yii::$app->request->post())) {
            $model->pad = $model->convertPadTableToArray();

            $returnHere = false; // признак необходимости вернуться в этот пакет

            // если нажата кнопка "Отправить на согласование"
            if (Yii::$app->request->post('order_ready') !== null) $model->cps_id = CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ;

            // если нажата кнопка "Согласовать"
            elseif (Yii::$app->request->post('order_approve') !== null) {
                $model->scenario = 'manager_approving';
                $model->cps_id = CorrespondencePackagesStates::STATE_УТВЕРЖДЕН;
            }

            // если нажата кнопка "Отказать"
            elseif (Yii::$app->request->post('order_reject') !== null) $model->cps_id = CorrespondencePackagesStates::STATE_ОТКАЗ;

            // если нажата кнопка "Отозвать согласование"
            elseif (Yii::$app->request->post('order_cancel') !== null) $model->cps_id = CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ;

            // если нажата кнопка "Подать повторно"
            elseif (Yii::$app->request->post('order_try_again') !== null) {
                $model->cps_id = CorrespondencePackagesStates::STATE_ЧЕРНОВИК;
                $returnHere = true;
            }

            if ($model->save())
                if ($returnHere)
                    return $this->redirect(['/correspondence-packages/update', 'id' => $id]);
                else
                    return $this->redirect(['/correspondence-packages']);

            $model->cps_id = $cps_id; // возвращаем статус
        }

        $vars = [
            'model' => $model,
            'contactEmails' => $model->arrayMapOfCompanyEmailsForSelect2(),
        ];

        // прикрепленные файлы
        $searchModel = new CorrespondencePackagesFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['cp_id' => $model->id]]);
        $dpFiles->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dpFiles->pagination = false;
        $vars['dpFiles'] = $dpFiles;

        if ($model->is_manual) {
            // история изменения статусов
            // если только это ручное отправление
            $searchModel = new CorrespondencePackagesHistorySearch();
            $dpHistory = $searchModel->search([$searchModel->formName() => ['cp_id' => $model->id]]);
            $dpHistory->setSort([
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['created_at'],
            ]);
            $dpHistory->pagination = false;
            $vars['dpHistory'] = $dpHistory;
        }

        return $this->render('update', $vars);
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
     * Перенаправляет на страницу формирования почтового отправления. В сессию записываются идентификаторы пакетов,
     * чтобы там уже их обработать.
     * @param $ids string идентификаторы пакетов
     * @return mixed
     */
    public function actionComposePackageBySelection($ids)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->session->set('ids_for_compose_package_' . Yii::$app->user->id, explode(',', $ids));
            Yii::$app->session->setFlash('warning', 'Не обновляйте страницу без надобности, поскольку идентификаторы пакетов будут утрачены.');
            return $this->redirect('/correspondence-packages/compose-package');
        }

        return '';
    }

    /**
     * Назначает виды документов, способ доставки, статус пакета документов и трек-номер (при необходимости).
     * @return mixed
     */
    public function actionComposePackage()
    {
        Url::remember(Yii::$app->request->referrer);
        $model = new ComposePackageForm();
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $pad = $model->tpPad;
                $model->tpPad = json_decode($model->convertPadTableToArray(), true);

                $fine = true;
                $order = null;
                $track_num = null;
                // создадим заказ и вычислим трек-номер, если в качестве способа доставки выбрана Почта России
                if ($model->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ) {
                    $order = TrackingController::pochtaRuCreateOrder($model->zip_code, $model->address, $model->contact_person);
                    if (false !== $order) {
                        $track_num = TrackingController::pochtaRuExtractTrackNumberFromOrder($order);
                        if (false == $track_num) {
                            $fine = false;
                            $model->addError('pd_id', 'Трек-номер определить не удалось.');
                        }
                    }
                    else {
                        $fine = false;
                        $model->addError('pd_id', 'Не удалось создать отправление.');
                    }
                }

                if ($fine) {
                    $packages = CorrespondencePackages::find()->where(['in', 'id', $model->packages_ids])->all();
                    foreach ($packages as $package) {
                        /* @var $package CorrespondencePackages */
                        $package->state_id = ProjectsStates::STATE_ОТПРАВЛЕНО;
                        if ($model->isReplacePad) {
                            $package->tpPad = $pad;
                            $package->pad = $package->convertPadTableToArray();
                        }
                        $package->pd_id = $model->pd_id;
                        if ($package->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ ||
                            $package->pd_id == PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS
                        ) {
                            $package->pochta_ru_order_id = $order;
                            $package->track_num = $track_num;
                        }

                        $package->save();
                    }
                    $this->goBack();
                }
            }
        }
        else {
            // извлекаем идентификаторы выделенных пакетов из сессии
            $key = 'ids_for_compose_package_' . Yii::$app->user->id;
            $ids = Yii::$app->session->get($key);
            if ($ids != null) {
                $model->packages_ids = $ids;
                Yii::$app->session->remove($key);
            }
            else {
                Yii::$app->session->setFlash('error', 'Ни один пакет не выбран.');
                return $this->redirect(['/correspondence-packages']);
            }

            // проверим адреса и контактных лиц
            // если адрес один, то отображаем в поле только для чтения, а если несколько, то не пускаем вообще
            // если контактные лица отличаются, то выводим select для указания конкретного контактного лица
            $address = [];
            $model->contactPersons = [];
            $packages = CorrespondencePackages::find()->where(['in', 'id', $model->packages_ids])->all();
            foreach ($packages as $package) {
                /* @var $package CorrespondencePackages */
                if ($package->address_id != null && !in_array($package->address_id, $address)) {
                    $address[] = $package->address_id;
                    $model->zip_code = $package->address->zip_code;
                    $model->address = $package->address->address_m;
                }

                if ($package->fo_contact_id != null && !in_array($package->contact_person, $model->contactPersons)) {
                    $model->contact_person = $package->contact_person;
                    $model->contactPersons[$package->fo_contact_id] = $package->contact_person;
                }
            }

            // если адресов несколько, то вообще не пускаем дальше
            if (count($address) > 1) {
                Yii::$app->session->setFlash('error', 'В выбранных пакетах ' . implode(',', $ids) . ' не один и тот же адрес, что недопустимо.');
                return $this->redirect(['/correspondence-packages']);
            }

            // пустая табличная часть
            $model->tpPad = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new \yii\db\Expression(0)])->orderBy('name_full')->asArray()->all();
        }

        return $this->render('compose_package', [
            'model' => $model,
        ]);
    }

    /**
     * Формирует и отдает форму добавления нового почтового адреса контрагента.
     * @param $id integer идентификатор пакета корреспонденции
     * @param $ca_id integer идентификатор контрагента
     * @return mixed
     */
    public function actionCreateAddressForm()
    {
        $model = new CounteragentsPostAddresses();

        if (Yii::$app->request->isAjax)
            if ($model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                if ($model->save()) return [
                    'id' => $model->id,
                    'address' => $model->address_m
                ];
            }
            else {
                $model->counteragent_id = Yii::$app->request->get('ca_id');

                return $this->renderAjax('/counteragents-post-addresses/_form', [
                    'model' => $model,
                ]);
            }

            return false;
    }

    /**
     * Загрузка файлов, перемещение их из временной папки, запись в базу данных.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionUploadFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $obj_id = Yii::$app->request->post('obj_id');
        $upload_path = CorrespondencePackagesFiles::getUploadsFilepath($obj_id);
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
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteFile($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = CorrespondencePackagesFiles::findOne($id);
            if ($model) {
                $parent_id = $model->cp_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderFiles($parent_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }

        /*
        $model = CorrespondencePackagesFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->cp_id;
            $model->delete();

            return $this->redirect(['/correspondence-packages/update', 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
        */
    }

    /**
     * Выполняет подбор контрагентов по части наименования, переданной в параметрах.
     * @param $q string подстрока поиска
     * @return array
     */
    public function actionCounteragentCastingByName($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $results = DirectMSSQLQueries::fetchCounteragents($q);
        if (count($results) > 0) {
            $profiles = ArrayHelper::map(Profile::find()->where(['is not', 'fo_id', null])->asArray()->all(), 'fo_id', 'user_id');

            foreach ($results as $index => $ca) {
                if (isset($profiles[$ca['managerId']]))
                    $results[$index]['managerId'] = $profiles[$ca['managerId']];
            }
        }

        return ['results' => $results];
    }

    /**
     * Выполняет выборку контактных лиц контрагента и возвращает ее.
     * @param $id integer идентификатор контрагента
     * @return array
     */
    public function actionFetchContactPersons($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $results = DirectMSSQLQueries::fetchCounteragentsContactPersons($id);
        if (count($results) > 0) return $results;

        return [];
    }

    /**
     * Делает выборку электронных ящиков контактного лица компании и возвращает первый из них.
     * @param $company_id
     * @param $contact_id
     * @return string
     */
    public function actionFetchContactEmails($company_id, $contact_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = foListEmailClient::find()->select('email')->where(['ID_COMPANY' => $company_id, 'ID_CONTACT_MAN' => $contact_id])->asArray()->column();
        if (count($query) > 0) {
            if (CpBlContactEmails::find()->select('email')->where(['email' => $query])->count() == 0) {
                return trim($query[0]);
            }
        }
        return '';
    }

    /**
     * Делает выборку адресов контрагентов с пустыми почтовыми индексами и нормализует каждый из них.
     */
    public function actionNormalizeEmptyAddresses()
    {
        $addresses = CounteragentsPostAddresses::find()->where(['zip_code' => null])->limit(50)->all();
        foreach ($addresses as $address) {
            $data = TrackingController::pochtaRuNormalizeAddress($address->src_address);
            if ($data !== false) {
                $address->zip_code = $data['index'];
                $address->address_m = TrackingController::implodePochtaRuAnswerToString($data);
                if (!$address->save()) var_dump($address->errors); else sleep(5);
            }
        }
    }

    public function actionComposeEnvelope($id)
    {
        $model = new PrintEnvelopeForm();

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

            if ($model->load(Yii::$app->request->post())) {
                $pdf = new Pdf([
                    'mode' => Pdf::MODE_UTF8,
                    'orientation' => Pdf::ORIENT_LANDSCAPE,
                    'content' => $this->render('@common/mail/layouts/html_envelope', ['model' => $model]),
                    'cssInline' => '* {
    margin: 0;
    padding: 0;
    font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
    box-sizing: border-box;
    font-size: 14px;
}',
                    'options' => ['title' => 'Краткие сведения о проекте'],
                ]);

                return $pdf->render();
            }
        }
        else {
            $model->cp_id = $this->findModel($id);

            return $this->render('compose_envelope', ['model' => $model]);
        }
    }

    public function actionTemp()
    {
        /*
        $model = CorrespondencePackages::findOne(5780);
        if ($model) {
            $model->sendClientNotification(CorrespondencePackages::NF_ARRIVED);
        }
        */
    }
}
