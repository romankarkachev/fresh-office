<?php

namespace backend\controllers;

use common\models\User;
use Yii;
use common\models\DirectMSSQLQueries;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\AssignFerrymanForm;
use common\models\FerrymanOrderForm;
use common\models\Ferrymen;
use common\models\Projects;
use common\models\ProjectsRatingsSearch;
use moonland\phpexcel\Excel;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Работа с проектами Fresh Office.
 */
class ProjectsController extends Controller
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
                        'actions' => ['direct-sql-counteragents-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['ratings', 'states-matrix'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        'actions' => [
                            'ferrymen-casting', 'cast-ferryman-by-region',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist'],
                    ],
                    [
                        'actions' => [
                            'index', 'update', 'ferrymen-casting', 'cast-ferryman-by-region',
                            'assign-ferryman-form', 'compose-ferryman-fields', 'assign-ferryman',
                            'create-order-by-selection',
                            'ferryman-order-form', 'validate-ferryman-order', 'export-ferryman-order',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'sales_department_manager', 'head_assist'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign-ferryman' => ['POST'],
                    'export-ferryman-order' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает список проектов.
     * Выборка через API Fresh Office.
     */
    public function actionIndex()
    {
        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Отображает список проектов с оценками, выставленными клиентами.
     * @return mixed
     */
    public function actionRatings()
    {
        $searchModel = new ProjectsRatingsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('ratings', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isDetailed' => !empty($searchModel->searchDetailed),
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Отображает матрицу проектов с их статусами, изменявшимся во времени. Первая колонка- id проекта, остальные колонки -
     * это статусы, в ячейках дата приобретения статуса.
     * @return mixed
     */
    public function actionStatesMatrix()
    {
        $searchModel = new foProjectsSearch();
        list ($dataProvider, $columns) = $searchModel->searchForMatrix(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(['/projects/states-matrix']);
                return false;
            }

            // уберем поле value, для экспорта оно не годится
            $searchModel->removeSortColumn($columns, 'value');

            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Матрица статусов проектов (сформирован '.date('Y-m-d в H i').').xlsx',
                'format' => 'Excel2007',
                'columns' => $columns,
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&'.Yii::$app->request->queryString;

            // для таблицы GridView уберем поле header, чтобы можно было сортировать по колонкам
            $searchModel->removeSortColumn($columns, 'header');

            return $this->render('states_matrix', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'columns' => $columns,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }

    /**
     * Finds the AppealSources model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return foProjects the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = foProjects::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Отображает форму подбора перевозчиков по региону.
     * @return mixed
     */
    public function actionFerrymenCasting()
    {
        return $this->render('fc');
    }

    /**
     * Выполняет подбор перевозчиков по переданному в параметрах региону.
     * @param $region_id integer идентификатор региона, по которому выполняется отбор перевозчиков
     * @param $is_detailed bool признак необходимости детализировать выборку
     * @return mixed
     */
    public function actionCastFerrymanByRegion($region_id, $is_detailed)
    {
        if (Yii::$app->request->isAjax) {
            $is_detailed = boolval(Json::decode($is_detailed));
            $region_id = intval($region_id);

            if ($is_detailed) {
                $data = Projects::find()
                    ->select([
                        'projects.*',
                        'ferrymanRep' => new Expression('CASE WHEN ferryman_id IS NULL THEN ferryman_origin ELSE ferrymen.name END')
                    ])->where(['region_id' => intval($region_id)])->joinWith('ferryman')->all();
            }
            else {
                $data = Projects::find()
                    ->select([
                        'cityNames' => 'GROUP_CONCAT(DISTINCT city.name ORDER BY city.name SEPARATOR ", ")',
                        'ferrymanRep' => new Expression('CASE WHEN ferryman_id IS NULL THEN ferryman_origin ELSE ferrymen.name END')
                    ])->where(['projects.region_id' => $region_id])
                    ->joinWith(['ferryman', 'city'])->orderBy('ferrymanRep')->groupBy(['ferrymanRep'])->all();
            }

            return $this->renderAjax('_fc_table', [
                'is_detailed' => $is_detailed,
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $data,
                    'key' => 'id',
                    'pagination' => [
                        'route' => '/projects/cast-ferryman-by-region',
                        'pageSize' => 50,
                    ],
                    'sort' => [
                        'defaultOrder' => ['id' => SORT_DESC],
                        'route' => '/projects/cast-ferryman-by-region',
                        'attributes' => [
                            'id',
                            'ferrymanRep',
                            'data',
                            'address',
                            'cityName',
                        ],
                    ],
                ]),
            ]);
        }
    }

    /**
     * Формирует и отдает форму назначения перевозичка.
     * @param $ids string идентификаторы проектов
     * @return mixed
     */
    public function actionCreateOrderBySelection($ids)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->session->set('ids_for_payment_order_' . Yii::$app->user->id, explode(',', $ids));
            return $this->redirect('/payment-orders/create');
        }

        return '';
    }

    /**
     * Формирует и отдает форму назначения перевозичка.
     * @param $ids string идентификаторы проектов
     * @return mixed
     */
    public function actionAssignFerrymanForm($ids)
    {
        if (Yii::$app->request->isAjax) {
            $model = new AssignFerrymanForm();
            $model->project_ids = explode(',', $ids);

            return $this->renderAjax('_assign_ferryman_form', [
                'model' => $model,
            ]);
        }

        return '';
    }

    /**
     * Формирует поля Водитель и Транспорт для выбранного пользователем перевозчика.
     * @param $type integer тип модели (1 - AssignFerrymanForm, 2 - FerrymanOrderForm)
     * @param $model_id integer идентификатор перевозчика или проекта
     * @return mixed
     */
    public function actionComposeFerrymanFields($type, $model_id)
    {
        if (Yii::$app->request->isAjax) {
            switch ($type) {
                case 1:
                    $model = new AssignFerrymanForm([
                        'ferryman_id' => $model_id,
                    ]);
                    break;
                case 2:
                    $model = new FerrymanOrderForm([
                        'ferryman_id' => $model_id,
                    ]);
                    break;
            }

            return $this->renderAjax('_assign_ferryman_fields', [
                'model' => $model,
                'form' => ActiveForm::begin(),
            ]);
        }

        return '';
    }

    /**
     * Назначает перевозчика, водителя и транспорт выбранным проектам.
     * @return mixed
     */
    public function actionAssignFerryman()
    {
        Url::remember(Yii::$app->request->referrer);
        if (Yii::$app->request->isPost) {
            $model = new AssignFerrymanForm();
            if ($model->load(Yii::$app->request->post())) {
                $driver = $model->driver->surname . ' ' . $model->driver->name;
                $driver = trim($driver);
                $driver .= ' ' . $model->driver->patronymic;
                $driver = trim($driver);
                if ($driver != '') {
                    // паспортные данные
                    $driver .= ', паспорт ' . $model->driver->pass_serie;
                    $driver = trim($driver);
                    if ($model->driver->pass_num != null && $model->driver->pass_num != '') $driver .= ' № ' . $model->driver->pass_num;
                    $driver = trim($driver);
                    if ($model->driver->pass_issued_at != null) $driver .= ' выдан ' . Yii::$app->formatter->asDate($model->driver->pass_issued_at, 'php:d.m.Y');
                    $driver = trim($driver);
                    $driver .= ' ' . $model->driver->pass_issued_by;
                    $driver = trim($driver);

                    // водительское удостоверение
                    $driver .= ', вод. удост. ' . $model->driver->driver_license;
                    if ($model->driver->dl_issued_at != null) $driver .= ' ' . Yii::$app->formatter->asDate($model->driver->dl_issued_at, 'php:d.m.Y');
                    $driver = trim($driver);
                }

                $data = $model->transport->representation . ' ' . $driver;
                if (DirectMSSQLQueries::assignFerryman($model->project_ids, $model->ferryman->name, $data))
                    Yii::$app->session->setFlash('success', 'Перевозчик успешно назначен в проекты ' . implode(',', $model->project_ids) . '.');
                else
                    Yii::$app->session->setFlash('error', 'Не удалось загрузить файлы.');

                $this->goBack();
            }
        }

        return '';
    }

    /**
     * Функция выполняет поиск контрагента по наименованию, переданному в параметрах.
     * @param $q string
     * @return array
     */
    public function actionDirectSqlCounteragentsList($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['results' => DirectMSSQLQueries::fetchCounteragents($q)];
    }

    /**
     * Рендерит форму создания заявки для перевозчика.
     * @param $id integer идентификатор проекта
     * @return mixed
     */
    public function actionFerrymanOrderForm($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = new FerrymanOrderForm([
                'project_id' => $id,
            ]);
            $model->hasVat = FerrymanOrderForm::VAT_MIT;
            $model->load_time = FerrymanOrderForm::DEFAULT_VALUE_LOAD_TIME;
            $model->special_conditions = FerrymanOrderForm::DEFAULT_VALUE_SPECIAL_CONDITIONS;
            $model->unload_address = FerrymanOrderForm::DEFAULT_VALUE_UNLOAD_ADDRESS;

            $project = DirectMSSQLQueries::fetchProjectsData($id);
            if (count($project) > 0) {
                // дополним модель данными из проекта
                $model->amount = $project['cost'];
                if ($project['ferryman'] != null) {
                    $ferryman = Ferrymen::findOne(['name_crm' => $project['ferryman']]);
                    if ($ferryman != null) $model->ferryman_id = $ferryman->id;
                }
            }

            return $this->renderAjax('_ferryman_order_form', [
                'model' => $model,
            ]);
        }

        return '';
    }

    /**
     * AJAX-валидация формы создания заявки для перевозчика.
     */
    public function actionValidateFerrymanOrder()
    {
        $model = new FerrymanOrderForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            echo Json::encode(ActiveForm::validate($model));
            Yii::$app->end();
        }
    }

    /**
     * Заполняет данными заявку по шаблону, заменяя в нем переменные. Отдает на скачивание готовый файл.
     * Выходной файл не сохраняется.
     * @throws NotFoundHttpException
     */
    public function actionExportFerrymanOrder()
    {
        $model = new FerrymanOrderForm();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // папка с шаблонами
            $tmplDir = Yii::getAlias('@uploads-export-templates-fs');

            // папка для выгрузки
            $path = Yii::getAlias('@uploads-documents-fs');
            if (!file_exists($path) && !is_dir($path)) mkdir($path, 0755);

            // полный путь к выгружаемому файлу
            $rn = 'ferrymanOrder_' . $model->project_id . '.docx';
            $r = $path . $rn; // для сохранения на диске
            $ffp = Yii::getAlias('@uploads-documents-fs') . $rn; // full file path, для отдачи пользователю
            $ffp = str_replace('\\', '/', $ffp);

            $project = DirectMSSQLQueries::fetchProjectsData($model->project_id);
            $address = $project['address'];
            if (($address == null || trim($address) == '')) {
                // если адрес в поле проекта не значится, то возьмем адрес из параметров проекта
                $key = array_search('Адрес', array_column($project['properties'], 'property'));
                if ($key !== false) $address = $project['properties'][$key]['value'];
                // а если адреса нет и в параметрах проекта, то это проблемы менеджера
            }

            // представление заказчика
            $customerRep = $model->organization->name_short;
            if ($model->organization->inn != null) $customerRep .= ', ИНН ' . $model->organization->inn;

            // описание груза
            $cargo = '';
            $key = array_search('Отход', array_column($project['properties'], 'property'));
            if ($key !== false) $cargo .= $project['properties'][$key]['value'];
            unset($key);

            $key = array_search('Вес и объем', array_column($project['properties'], 'property'));
            if ($key !== false) $cargo .= (trim($cargo) != '' ? ' ' : '') . $project['properties'][$key]['value'];
            unset($key);

            // упаковка
            $packing = '';
            $key = array_search('Упаковка отхода', array_column($project['properties'], 'property'));
            if ($key !== false) $packing = $project['properties'][$key]['value'];
            unset($key);

            // стоимость
            $spellout = Yii::$app->formatter->asSpellout($model->amount);
            /*
            $spellout = str_replace('целых', 'руб.', $spellout);
            $spellout = str_replace('сотых', 'коп.', $spellout);
            */
            $spellout = str_replace('целых', ' ', $spellout);
            $spellout = str_replace('сотых', '', $spellout);
            $amount = Yii::$app->formatter->asDecimal($model->amount) . ' (' . $spellout . ') руб. б/нал. ' . ($model->hasVat == 0 ? 'без НДС' : 'с НДС') . ' 10 б/д по ФТТН';

            // представление транспорнтого средства
            $transportRep = '';
            if ($model->transport != null) {
                $transportRep = $model->transport->brandName . ' ';
                if ($model->transport->rn != null && $model->transport->rn != '')
                    $transportRep .= 'г/н ' . $model->transport->rn;
                if ($model->transport->trailer_rn != null && $model->transport->trailer_rn != '')
                    $transportRep .= ', прицеп ' . $model->transport->trailer_rn;
            }

            // представление водителя
            $driverRep = '';
            if ($model->driver != null) {
                $driverRep = $model->driver->surname . ' ' . $model->driver->name . ' ' . $model->driver->patronymic;
                $driverRep .= ' паспорт ' . $model->driver->pass_serie . ' ' . $model->driver->pass_num .
                    ' выдан ' . Yii::$app->formatter->asDate($model->driver->pass_issued_at, 'php:d.m.Y') . ' ' .
                    $model->driver->pass_issued_by;
                $driverRep .= ', тел. ' . Ferrymen::normalizePhone($model->driver->phone);
                if ($model->driver->phone2 != null && $model->driver->phone2 != '')
                    $driverRep .= ', ' . Ferrymen::normalizePhone($model->driver->phone2);
            }

            $arraySubst = [
                '%PROJECT_NUM%' => $model->project_id,
                '%PROJECT_DATE%' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y г.'),
                '%CUSTOMER_REP%' => $customerRep,
                '%FERRYMAN_REP%' => (!empty($model->ferryman->name_short) ? $model->ferryman->name_short : $model->ferryman->name) . ', ИНН ' . $model->ferryman->inn,
                '%LOAD_ADDRESS%' => $address,
                '%UNLOAD_ADDRESS%' => $model->unload_address,
                '%LOAD_DATE%' => Yii::$app->formatter->asDate($project['vivozdate'], 'php:d.m.Y'),
                '%LOAD_TIME%' => $model->load_time,
                '%CARGO_DESCRIPTION%' => $cargo,
                '%PACKING%' => $packing,
                '%AMOUNT%' => $amount,
                '%SPECIAL_CONDITIONS%' => $model->special_conditions,
                '%TRANSPORT_REP%' => $transportRep,
                '%DRIVER_REP%' => $driverRep,
            ];

            // имя шаблона определяется в соответствии с ОГРН выбранной организации
            $tmplName = 'tmpl_ferrymanOrder.docx';
            if (!empty($model->organization) && !empty($model->organization->ogrn)) {
                $tmplName = 'tmpl_ferrymanOrder_' . $model->organization->ogrn . '.docx';
            }

            $docx_gen = new \DocXGen;
            $docx_gen->docxTemplate($tmplDir . $tmplName, $arraySubst, $r);

            if (preg_match('/^[a-z0-9]+\.[a-z0-9]+$/i', $rn) !== 0 || !is_file("$ffp")) {
                throw new NotFoundHttpException('Запрошенный файл не существует.');
            }

            \Yii::$app->response->sendFile($ffp, $rn, ['mimeType' => 'application/docx']);
            if (file_exists($ffp)) unlink($ffp);
        }
    }
}
