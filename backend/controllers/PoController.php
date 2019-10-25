<?php

namespace backend\controllers;

use Yii;
use common\models\Po;
use common\models\PoSearch;
use common\models\PoFiles;
use common\models\PoFilesSearch;
use common\models\PaymentOrdersStates;
use common\models\PoEi;
use common\models\PoEip;
use common\models\PoProperties;
use common\models\PoValues;
use common\models\PoPop;
use common\models\PoPopSearch;
use common\models\UsersEiApproved;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PoController implements the CRUD actions for Po model.
 */
class PoController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'po';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Платежные ордеры (бюджет)';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    /**
     * URL для загрузки файлов через ajax
     */
    const URL_UPLOAD_FILES = 'upload-files';
    const URL_UPLOAD_FILES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPLOAD_FILES];

    /**
     * URL для скачивания приаттаченных файлов
     */
    const URL_DOWNLOAD_FILE = 'download-file';

    /**
     * URL для предварительного просмотра файлов через ajax
     */
    const URL_PREVIEW_FILE = 'preview-file';
    const URL_PREVIEW_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_PREVIEW_FILE];

    /**
     * URL для удаления файла через ajax
     */
    const URL_DELETE_FILE = 'delete-file';
    const URL_DELETE_FILE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_FILE];

    /**
     * URL для рендера блока со свойствами статьи расходов
     */
    const URL_RENDER_PROPERTIES = 'render-properties';
    const URL_RENDER_PROPERTIES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_PROPERTIES];

    /**
     * URL для изменения статуса на "Оплачено" на лету
     */
    const URL_SET_PAID_ON_THE_FLY = 'set-paid-on-the-fly';
    const URL_SET_PAID_ON_THE_FLY_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_SET_PAID_ON_THE_FLY];

    /**
     * URL для интерактивного удаления привязки значения свойства к статье расходов платежного ордера
     */
    const URL_DELETE_VALUE_LINK = 'delete-value-link';
    const URL_DELETE_VALUE_LINK_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_VALUE_LINK];

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
                        'actions' => [self::URL_DOWNLOAD_FILE],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update', self::URL_RENDER_PROPERTIES, self::URL_SET_PAID_ON_THE_FLY, self::URL_UPLOAD_FILES, self::URL_PREVIEW_FILE,
                            self::URL_DELETE_FILE, self::URL_DELETE_VALUE_LINK,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'assistant', 'accountant', 'accountant_b', 'ecologist', 'ecologist_head', 'prod_department_head', 'tenders_manager'],
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
                    self::URL_DELETE_FILE => ['POST'],
                    self::URL_DELETE_VALUE_LINK => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку свойств и значений свойств, которыми описана статья расходов, переданная в параметрах.
     * SELECT * FROM `po_values` WHERE `property_id` IN (SELECT `property_id` FROM `po_eip` WHERE `ei_id` = 5)
     * @param $ei PoEi
     * @return array
     */
    public function fetchExpenditureItemPropertiesValuesAsArray($ei)
    {
        return PoValues::find()
            ->select([
                PoValues::tableName() . '.id',
                PoValues::tableName() . '.name',
                'property_id',
                'propertyName' => PoProperties::tableName() . '.name',
            ])
            ->where(['property_id' => PoEip::find()->select('property_id')->where(['ei_id' => $ei->id])])
            ->leftJoin(PoProperties::tableName(), PoProperties::tableName() . '.id = ' . PoValues::tableName() . '.property_id')
            ->orderBy(PoProperties::tableName() . '.name,' . PoValues::tableName() . '.id')
            ->asArray()->all();
    }

    /**
     * Делает выборку свойств и значений свойств платежного ордера, переданного в параметрах.
     * @param $model Po
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function fetchPropertiesAsDataProvider($model)
    {
        $searchModel = new PoPopSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['po_id' => $model->id]]);
        $dataProvider->pagination = false;
        $dataProvider->sort = false;
        return $dataProvider;
    }

    /**
     * Делает выборку свойств и значений свойств платежного ордера в ввиде массива, с целью возможного редактирования
     * их состава.
     * @param $model Po
     * @return array
     */
    public function fetchPropertiesAsFilledArray($model)
    {
        // свойства, которые в настоящий момент привязаны к статье расходов
        $pureProperties = $this->fetchExpenditureItemPropertiesValuesAsArray($model->ei);

        // свойства, которые фактически привязаны к статье расходов конкретного платежного ордера
        foreach (PoPop::find()->joinWith(['property', 'value'])->where(['po_id' => $model->id])->all() as $property) {
            $propertyFound = false;
            foreach ($pureProperties as $index => $pureProperty) {
                if ($pureProperty['property_id'] == $property->property_id && $pureProperty['id'] == $property->value_id) {
                    $pureProperties[$index]['selected'] = true;
                    $pureProperties[$index]['link_id'] = $property->id;
                    $propertyFound = true;
                    break;
                }
            }

            if (!$propertyFound) {
                $pureProperties[] = [
                    'id' => $property['value_id'],
                    'name' => $property['valueName'],
                    'property_id' => $property['property_id'],
                    'propertyName' => $property['propertyName'],
                    'selected' => true,
                    'link_d' => null,
                ];
            }
        }

        return $pureProperties;
    }

    /**
     * Lists all Po models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $params = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

        if (Yii::$app->user->can('accountant_b')) {
            // для бухгалтеров по бюджету прикрепляем дополнительную переменную, в которой будут доступные для согласования
            // без ведома руководства статьи расходов
            if (!empty(Yii::$app->user->identity->profile->po_maa)) {
                $params['eiApproved'] = UsersEiApproved::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])->column();
                $params['eiAmount'] = (float)Yii::$app->user->identity->profile->po_maa;
            }
        }

        return $this->render('index', $params);
    }

    /**
     * Creates a new Po model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Po();

        if ($model->load(Yii::$app->request->post())) {
            $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК;
            if ($model->save()) {
                if (!empty($model->propertiesValues)) {
                    foreach ($model->propertiesValues as $index => $row) {
                        (new PoPop([
                            'po_id' => $model->id,
                            'ei_id' => $model->ei_id,
                            'property_id' => $index,
                            'value_id' => $row['value_id'],
                        ]))->save();
                    }
                }
                return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'dpProperties' => [],
        ]);
    }

    /**
     * Updates an existing Po model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $state = $model->state_id;

        if ($model->load(Yii::$app->request->post())) {
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

                if ($model->save()) {
                    if (Yii::$app->request->post('order_repeat') !== null) {
                        return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $id]);
                    }
                    else {
                        return $this->redirect(self::ROOT_URL_AS_ARRAY);
                    }
                }
                else $model->state_id = $state; // возвращаем статус
            }
            else {
                // возвращаем статус
                $model->state_id = $state;
            }
        }

        // параметры, которые передаются в форму
        $params = [
            'model' => $model,
        ];

        if (Yii::$app->user->can('accountant_b')) {
            // для бухгалтеров по бюджету прикрепляем дополнительную переменную, в которой будут доступные для согласования
            // без ведома руководства статьи расходов
            if (!empty(Yii::$app->user->identity->profile->po_maa)) {
                $params['eiApproved'] = UsersEiApproved::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])->column();
                $params['eiAmount'] = Yii::$app->user->identity->profile->po_maa;
            }
        }

        // файлы к объекту
        $searchModel = new PoFilesSearch();
        $dpFiles = $searchModel->search([$searchModel->formName() => ['po_id' => $model->id]]);
        $dpFiles->setSort([
            'defaultOrder' => ['uploaded_at' => SORT_DESC],
        ]);
        $dpFiles->pagination = false;
        $params['dpFiles'] = $dpFiles;

        $formName = 'update';
        switch ($model->state_id) {
            case PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК:
                $params['dpProperties'] = $this->fetchPropertiesAsFilledArray($model);
                break;
            default:
                $formName = 'view';
                $params['dpProperties'] = $this->fetchPropertiesAsDataProvider($model);
                break;
        }

        return $this->render($formName, $params);
    }

    /**
     * Deletes an existing Po model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the Po model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Po the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Po::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
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
        $upload_path = PoFiles::getUploadsFilepath($obj_id);
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
                $filename = mb_strtolower(Yii::$app->security->generateRandomString() . '.' . $ext, 'utf-8');
                $filepath = $upload_path . '/' . $filename;
                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $fu = new PoFiles();
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
            $model = PoFiles::findOne($id);
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
        $model = PoFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return \yii\helpers\Html::img(Yii::getAlias('@uploads-correspondence-packages') . '/' . $model->fn, ['width' => '100%']);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_FILE, 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
        }

        return false;
    }

    /**
     * Удаляет файл, привязанный к объекту.
     * @param $id
     * @return Response
     * @throws NotFoundHttpException если файл не будет обнаружен
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFile($id)
    {
        $model = PoFiles::findOne($id);
        if ($model != null) {
            $record_id = $model->po_id;
            $model->delete();

            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $record_id]);
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }

    /**
     * Рендерит блок со свойствами, которыми описана переданная в параметрах статья расходов.
     * render-properties
     * @param $ei_id integer статья расходов
     * @return mixed
     */
    public function actionRenderProperties($ei_id)
    {
        if (Yii::$app->request->isAjax) {
            $expenditureItem = PoEi::findOne($ei_id);
            if ($expenditureItem) {
                return $this->renderAjax('_properties_block', ['properties' => $this->fetchExpenditureItemPropertiesValuesAsArray($expenditureItem)]);
            }

            return '';
        }
    }

    /**
     * Выполняет интерактивное изменение статуса платежного ордера на "Оплачено".
     * set-paid-on-the-fly
     * @param $po_id integer идентфикатор платежного ордера
     * @param $state_id integer новый статус, который необходимо присвоить
     * @return mixed
     */
    public function actionSetPaidOnTheFly($po_id, $state_id)
    {
        $state_id = intval($state_id);
        $state = PaymentOrdersStates::findOne($state_id);

        $po_id = intval($po_id);
        $model = Po::findOne($po_id);
        if ($state != null && $model != null) {
            if (Yii::$app->user->can('accountant_b')) {
                // бухгалтер по бюджету должен обладать правом согласования платежей, проверяется следующим образом:
                if (
                    !in_array($model->ei_id, UsersEiApproved::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])->column()) ||
                    $model->amount > (float)Yii::$app->user->identity->profile->po_maa)
                    return false;
            }

            $model->state_id = $state_id;
            return $model->save(false);
        }

        return false;
    }

    /**
     * @param $id integer идентификатор привязки
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @return bool
     */
    public function actionDeleteValueLink($id)
    {
        $model = PoPop::findOne($id);
        if ($model) {
            return $model->delete();
        }
    }
}
