<?php

namespace backend\controllers;

use common\models\AuthItem;
use Yii;
use common\models\IncomingMail;
use common\models\IncomingMailSearch;
use common\models\IncomingMailFiles;
use common\models\Companies;
use common\models\Ferrymen;
use common\models\foCompany;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * IncomingMailController implements the CRUD actions for IncomingMail model.
 */
class IncomingMailController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = 'incoming-mail';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Входящая корреспонденция';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    /**
     * URL для рендеринга поля со входящим номером
     */
    const URL_RENDER_FIELD_INC_NUM = 'render-field-inc-num';
    const URL_RENDER_FIELD_INC_NUM_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_RENDER_FIELD_INC_NUM];

    /**
     * URL для подбора контрагента из нескольких источников
     */
    const URL_CASTING_COUNTERAGENT_MULTI = 'casting-counteragents-multi-for-type-ahead';
    const URL_CASTING_COUNTERAGENT_MULTI_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_CASTING_COUNTERAGENT_MULTI];

    /**
     * URL для загрузки файлов через ajax
     */
    const URL_UPLOAD_FILES = 'upload-files';
    const URL_UPLOAD_FILES_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_UPLOAD_FILES];

    /**
     * URL для скачивания приаттаченных файлов
     */
    const URL_DOWNLOAD_FILE = 'download-file';

    /**
     * URL для предварительного просмотра файлов через ajax
     */
    const URL_PREVIEW_FILE = 'preview-file';
    const URL_PREVIEW_FILE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_PREVIEW_FILE];

    /**
     * URL для удаления файла через ajax
     */
    const URL_DELETE_FILE = 'delete-file';
    const URL_DELETE_FILE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_DELETE_FILE];

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
                            self::URL_CASTING_COUNTERAGENT_MULTI,
                            self::URL_DOWNLOAD_FILE, self::URL_UPLOAD_FILES, self::URL_PREVIEW_FILE, self::URL_DELETE_FILE,
                        ],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [AuthItem::ROLE_ROOT, AuthItem::ROLE_ASSISTANT, AuthItem::ROLE_LOGIST],
                    ],
                    [
                        'actions' => ['create', self::URL_UPDATE, self::URL_RENDER_FIELD_INC_NUM],
                        'allow' => true,
                        'roles' => [AuthItem::ROLE_ROOT, AuthItem::ROLE_ASSISTANT],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => [AuthItem::ROLE_ROOT],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all IncomingMail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new IncomingMailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new IncomingMail model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new IncomingMail();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
            }
        }
        else {
            $model->inc_date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
            $model->direction = IncomingMail::DIRECTION_IN;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing IncomingMail model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(self::URL_ROOT_AS_ARRAY);
            }
        }
        else {
            $model->counteragent = $model->ca_id;
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing IncomingMail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::URL_ROOT_AS_ARRAY);
    }

    /**
     * Finds the IncomingMail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IncomingMail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IncomingMail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Рендерит поле со входящим номером.
     * @param int $org_id идентификатор организации
     * @return mixed
     */
    public function actionRenderFieldIncNum($org_id)
    {
        $model = new IncomingMail(['org_id' => $org_id]);
        $model->calcNextNumber('im_num_tmpl');

        return $this->renderAjax('_field_inc_num', [
            'model' => $model,
            'form' => \yii\bootstrap\ActiveForm::begin(),
        ]);
    }

    /**
     * Функция выполняет подбор контрагентов по наименованию от значения переданного в параметрах.
     * Для виджетов TypeAhead.
     * casting-counteragents-multi-for-type-ahead
     * @param $q string
     * @return array
     */
    public function actionCastingCounteragentsMultiForTypeAhead($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($q)) {
            return ['results' => []];
        }

        $result = [];

        // извлекаем первую группу контрагентов - из соответствующего локального справочника
        $query = Companies::find()->select([
            'id',
            'text' => 'name',
            'src' => new Expression('"' . IncomingMail::CA_SOURCES_КОНТРАГЕНТЫ . '"'),
            'groupName' => new Expression('"Найдено в справочнике контрагентов"'),
        ])->andFilterWhere([
            'or',
            ['like', 'name', $q],
            ['like', 'name_short', $q],
            ['like', 'name_full', $q],
            ['like', 'inn', $q],
            ['like', 'ogrn', $q],
        ])->orderBy('name');

        $result = ArrayHelper::merge($result, $query->asArray()->all());
        unset($query);

        // вторая группа - перевозчики, также выборка из базы данных веб-приложения
        $query = Ferrymen::find()->select([
            'id',
            'text' => 'name',
            'src' => new Expression('"' . IncomingMail::CA_SOURCES_ПЕРЕВОЗЧИКИ . '"'),
            'groupName' => new Expression('"Найдено в справочнике перевозчиков"'),
        ])->andFilterWhere([
            'or',
            ['like', 'name', $q],
            ['like', 'name_full', $q],
            ['like', 'name_short', $q],
            ['like', 'inn', $q],
            ['like', 'ogrn', $q],
        ])->orderBy('name');

        $result = ArrayHelper::merge($result, $query->asArray()->all());
        unset($query);

        // третья группа - это контрагенты, найденны в справочнике CRM Fresh Office
        $query = foCompany::find()->select([
            'id' => 'ID_COMPANY',
            'text' => 'COMPANY_NAME',
        ])->andFilterWhere([
            'or',
            ['like', 'COMPANY_NAME', $q],
            ['like', 'INN', $q],
        ])->orderBy('COMPANY_NAME');
        // для MS SQL требуется обработка:
        foreach ($query->asArray()->all() as $item) {
            $result[] = [
                'id' => (string)$item['id'],
                'text' => $item['text'],
                'src' => (string)IncomingMail::CA_SOURCES_FRESH_OFFICE,
                'groupName' => 'Найдено в справочнике CRM',
            ];
        }
        unset($query);

        $array = [];
        $current_group = -1;
        $children = [];
        $prev_name = '';
        foreach ($result as $item) {
            if ($item['groupName'] != $current_group && $current_group != -1) {
                $array[] = [
                    'text' => $prev_name . (count($children) > 0 ? ': ' . count($children) : ''),
                    'children' => $children,
                ];
                $children = [];
            }
            $prev_name = $item['groupName'];
            $children[] = [
                'id' => $item['id'],
                'text' => $item['text'],
                'src' => $item['src'],
            ];
            $current_group = $item['groupName'];
        }
        if (count($children) > 0) {
            $array[] = [
                'text' => $prev_name . (count($children) > 0 ? ': ' . count($children) : ''),
                'children' => $children,
            ];
        }

        return ['results' => $array];
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
        $object = IncomingMail::findOne($obj_id);
        if ($object) {
            $upload_path = IncomingMailFiles::getUploadsFilepath($object);
            if ($upload_path === false) return 'Невозможно создать папку для хранения загруженных файлов!';

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
                        $fu = new IncomingMailFiles();
                        $fu->im_id = $obj_id;
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
            $model = IncomingMailFiles::findOne($id);
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
        $model = IncomingMailFiles::findOne($id);
        if ($model != null) {
            if ($model->isImage())
                return \yii\helpers\Html::img(Yii::getAlias('@uploads-incoming-mail') . '/' . date('Y', $model->uploaded_at) . '/' . date('m', $model->uploaded_at) . '/' . date('d', $model->uploaded_at) . '/' . $model->im_id . '/' . $model->fn, ['width' => '100%']);
            else
                return '<iframe src="http://docs.google.com/gview?url=' . Yii::$app->urlManager->createAbsoluteUrl(['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_DOWNLOAD_FILE, 'id' => $id]) . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
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
        $model = IncomingMailFiles::findOne($id);
        if ($model != null) {
            $direction = $model->im->direction;
            $record_id = $model->im_id;
            $model->delete();

            if ($direction == IncomingMail::DIRECTION_OUT) {
                return $this->redirect(['/' . OutcomingMailController::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $record_id]);
            }
            else {
                return $this->redirect(['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $record_id]);
            }
        }
        else
            throw new NotFoundHttpException('Файл не обнаружен.');
    }
}
