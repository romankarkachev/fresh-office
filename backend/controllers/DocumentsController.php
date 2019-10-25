<?php

namespace backend\controllers;

use Yii;
use common\models\Documents;
use common\models\DocumentsSearch;
use common\models\DocumentsHk;
use common\models\DocumentsTp;
use common\models\HandlingKinds;
use common\models\Products;
use common\models\ProductsExcludes;
use common\models\ProductsImport;
use common\models\FreshOfficeAPI;
use common\models\DadataAPI;
use common\models\DocumentAPPGenerationForm;
use common\models\DocumentTtnGenerationForm;
use common\models\Ferrymen;
use common\models\foListDocuments;
use common\models\foListDocumentsTp;
use common\models\foProjects;
use common\models\ProjectsTypes;
use common\models\foProjectsParameters;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends Controller
{
    /**
     * Максимальное количество строк, которое можно вывести в шаблон Акт приема-передачи
     */
    const TEMPLATE_APP_MAX_ROWS_COUNT = 70;

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
                        'actions' => [
                            'export', 'generate-ttn', 'generate-app',
                        ],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'sales_department_manager'],
                    ],
                    [
                        'actions' => [
                            'index', 'create', 'update', 'delete', 'clear',
                            'direct-sql-get-project-data', 'direct-sql-get-project-table-part',
                            'render-row', 'delete-row', 'api-get-project-data', 'api-get-project-table-part',
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
                    'delete-row' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Documents models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DocumentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new Documents model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Documents();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/documents']);
        } else {
            $model->author_id = Yii::$app->user->id;
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Documents model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // виды обращения с отходами
        $hks = HandlingKinds::find()->all();

        // виды обращения с отходами к документу
        $hk = DocumentsHk::find()->select('hk_id')->where(['doc_id' => $model->id])->column();

        if ($model->load(Yii::$app->request->post())) {

            // пришедшие снаружи идентификаторы переводим в модели строк табличной части
            $dtp = $model->makeTpModelsFromPostArray();

            // пришедшие снаружи идентификаторы видов обращений переводим в соответствующие модели
            $dhk = $model->makeHkModelsFromPostArray();

            if ($model->validate()) {
                if ($model->save()) {
                    // очищаем табличную часть и записываем заново
                    DocumentsTp::deleteAll(['doc_id' => $model->id]);
                    if (!empty($dtp)) {
                        foreach ($dtp as $tp) {
                            $row = new DocumentsTp();
                            $row->attributes = $tp->attributes;
                            $row->save();
                        }
                    }

                    // очищаем отметки с видами обращения и записываем заново
                    DocumentsHk::deleteAll(['doc_id' => $model->id]);
                    if (!empty($dhk)) {
                        foreach ($dhk as $hk) {
                            $row = new DocumentsHk();
                            $row->attributes = $hk->attributes;
                            $row->save();
                        }
                    }
                }
            }
            else {
                $dhkt = [];
                foreach ($dhk as $hk) $dhkt[] = $hk->hk_id;

                return $this->render('update', [
                    'model' => $model,
                    'tprows' => $dtp,
                    'hkrows' => $dhkt,
                    'hks' => $hks,
                ]);
            }

            return $this->redirect(['/documents']);
        } else {
            // табличная часть к документу
            $tp = DocumentsTp::find()->where(['doc_id' => $model->id])->all();

            return $this->render('update', [
                'model' => $model,
                'tprows' => $tp,
                'hkrows' => $hk,
                'hks' => $hks,
            ]);
        }
    }

    /**
     * Deletes an existing Documents model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/documents']);
    }

    /**
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Производит удаление всех имеющихся в наличии документов с их табличными частями.
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionClear()
    {
        $count = 0;

        $records = Documents::find()->all();
        foreach ($records as $record) {
            if (false !== $record->delete()) {
                $count++;
            }
        }

        if ($count > 0) {
            Yii::$app->session->setFlash('info', 'Объектов удалено: ' . $count . '.');
        }

        return $this->redirect(['/documents']);
    }

    /**
     * Возвращает результат рендера строки табличной части.
     * @param $counter integer
     * @return string
     */
    public function actionRenderRow($counter)
    {
        $tp = new DocumentsTp();

        return $this->renderAjax('_tp', [
            'model' => $tp,
            'counter' => $counter+1,
        ]);
    }

    /**
     * Выполняет удаление строки табличной части из документа.
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteRow($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($id != null) {
            DocumentsTp::deleteAll(['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Выполняет запрос данных проекта через подключение к SQL-серверу к базе данных FreshOffice.
     * @param $project_id
     * @return array
     */
    public function actionDirectSqlGetProjectData($project_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fo_project = Documents::makeDirectSQL_ProjectData($project_id);
        if (count($fo_project) > 0) {
            $fo_project = $fo_project[0];
            $project_date = date('d.m.Y', strtotime($fo_project['DATE_CREATE_PROGECT']));

            return [
                'results' => [
                    [
                        'id' => $project_id,
                        'text' => 'Проект № ' . $project_id . ' от ' . $project_date . ' (' . $fo_project['company_name'] . ')',
                        'customer_id' => $fo_project['ID_COMPANY'],
                        'state_name' => $fo_project['state_name'],
                        'type_name' => $fo_project['type_name'],
                        'company_name' => $fo_project['company_name'],
                        'contract' => ($fo_project['contract_basic'] == null || $fo_project['contract_basic'] == '' ? '' : $fo_project['contract_basic'] . ($fo_project['contract_date'] == null ? '' : ' (до ' . Yii::$app->formatter->asDate($fo_project['contract_date'], 'php:d.m.Y') . ')')),
                    ]
                ]
            ];
        }
        else if (isset($fo_project['error']))
            return [
                'results' => []
            ];
    }

    /**
     * Выполняет запрос данных табличной части проекта через подключение к SQL-серверу к базе данных FreshOffice.
     * @param $project_id
     * @return string
     */
    public function actionDirectSqlGetProjectTablePart($doc_id, $project_id, $counter)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fo_projects = Documents::makeDirectSQL_ProjectTablePart($project_id);
        if (count($fo_projects) > 0) {
            // сделаем выборку слов-исключений
            $excludes = ProductsExcludes::find()->select('name')->asArray()->column();

            $result = '';
            foreach ($fo_projects as $project) {
                $fo_name = trim($project['DESCRIPTION_TOVAR']);

                // проверим, не встречаются ли слова-исключения в наименовании текущего товара
                foreach ($excludes as $index => $exclude) {
                    if (mb_stripos($fo_name, $exclude) !== false)
                        // если встречаются, то такой товар вообще не будем загружать в документ
                        continue 2;
                }

                $model = new DocumentsTp();
                $product = null;
                // идентификация по коду Fresh Office
                $fo_id = trim($project['ID_TOVAR']);
                if ($fo_id != '') $product = Products::find()->where(['fo_id' => $fo_id])->one();
                // если не будет обнаружен, попытаемся идентифицировать по наименованию
                if ($product == null) $product = Products::find()->where(['fo_name' => $fo_name])->one();
                if ($product == null) {
                    // если не идентифицирован ни по коду, ни по наименованию, то создадим
                    $product = new Products();
                    $product->author_id = Yii::$app->user->id;
                    $product->name = ProductsImport::cleanName($project['DESCRIPTION_TOVAR']);
                    $product->fo_name = trim($project['DESCRIPTION_TOVAR']);
                    // если не удастся сохранить, то пропустим такую позицию
                    if (!$product->save()) continue;
                }
                $model->product_id = $product->id;
                $model->dc = $product->dc;
                $model->quantity = floatval($project['KOLVO']);

                $result .= $this->renderAjax('_tp', [
                    'document' => new Documents(),
                    'model' => $model,
                    'counter' => $counter + 1,
                ]);

                $counter++;
            }

            return [
                'results' => $result,
                'counter' => $counter,
            ];
        }
        else
            // товаров нет, бывает
            return [
                'results' => []
            ];
    }

    /**
     * Выполняет запрос данных проекта через API FreshOffice.
     * Возвращает массив проектов, соответствующих значению в параметрах.
     * @param $project_id
     * @return array
     */
    public function actionApiGetProjectData($project_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fo_project = json_decode(FreshOfficeAPI::makeGetRequestToApi('project(' . $project_id . ')', 'id_list_project_company,id_company,date_create_project'), true);
        if (isset($fo_project['d'])) {
            $fo_project = $fo_project['d'];
            $company = json_decode(FreshOfficeAPI::makeGetRequestToApi('companies(' . $fo_project['id_company'] . ')', 'id,name'), true);
            $date = new \DateTime($fo_project['date_create_project']);
            $project_date = $date->format('d.m.Y');

            return [
                'results' => [
                    [
                        'id' => $project_id,
                        'text' => 'Проект № ' . $project_id . ' от ' . $project_date . ' (' . $company['d']['name'] . ')',
                        'customer_id' => $fo_project['id_company'],
                        //'contract_id' => $fo_project['id_company'],
                    ]
                ]
            ];
        }
        else if (isset($fo_project['error']))
            return [
                'results' => []
            ];
    }

    /**
     * Выполняет запрос данных проекта через API FreshOffice.
     * Возвращает массив проектов, соответствующих значению в параметрах.
     * @param $project_id
     * @return string
     */
    public function actionApiGetProjectTablePart($doc_id, $project_id, $counter)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fo_projects = json_decode(FreshOfficeAPI::makeGetRequestToApi('tovar_project', null, 'id_list_project_company eq ' . $project_id), true);
        if (isset($fo_projects['d'])) {
            $fo_projects = $fo_projects['d']['results'];
            if (count($fo_projects) == 0)
                // товаров нет, бывает
                return [
                    'results' => []
                ];

            // сделаем выборку слов-исключений
            $excludes = ProductsExcludes::find()->select('name')->asArray()->column();

            $result = '';
            foreach ($fo_projects as $project) {
                $fo_name = trim($project['discription_tovar']);

                // проверим, не встречаются ли слова-исключения в наименовании текущего товара
                foreach ($excludes as $index => $exclude) {
                    if (mb_stripos($fo_name, $exclude) !== false)
                        // если встречаются, то такой товар вообще не будем загружать в документ
                        continue 2;
                }

                $model = new DocumentsTp();
                $product = null;
                // идентификация по коду Fresh Office
                $fo_id = trim($project['id_tovar']);
                if ($fo_id != '') $product = Products::find()->where(['fo_id' => $fo_id])->one();
                // если не будет обнаружен, попытаемся идентифицировать по наименованию
                if ($product == null) $product = Products::find()->where(['fo_name' => $fo_name])->one();
                if ($product == null) {
                    // если не идентифицирован ни по коду, ни по наименованию, то создадим
                    $product = new Products();
                    $product->author_id = Yii::$app->user->id;
                    $product->name = ProductsImport::cleanName($project['discription_tovar']);
                    $product->fo_name = trim($project['discription_tovar']);
                    // если не удастся сохранить, то пропустим такую позицию
                    if (!$product->save()) continue;
                }
                $model->product_id = $product->id;
                $model->dc = $product->dc;
                $model->quantity = floatval($project['kolvo']);

                $result .= $this->renderAjax('_tp', [
                    'document' => new Documents(),
                    'model' => $model,
                    'counter' => $counter + 1,
                ]);

                $counter++;
            }

            return [
                'results' => $result,
                'counter' => $counter,
            ];
        }
        else if (isset($fo_project['error']))
            return [
                'results' => []
            ];
    }

    /**
     * Формирует документ Microsoft Word из шаблона, заполняя в нем необходимые данные.
     * Отдает файл на скачивание.
     * @param $doc_id integer
     * @throws NotFoundHttpException если перед скачиванием файла не окажется на месте
     * @return mixed
     */
    public function actionExport($doc_id)
    {
        $model = Documents::findOne($doc_id);
        if ($model == null) return $this->redirect(['/documents']);

        // папка с шаблонами
        $tmplDir = Yii::getAlias('@uploads-export-templates-fs');

        // папка для выгрузки
        $path = Yii::getAlias('@uploads-documents-fs');
        if (!file_exists($path) && !is_dir($path)) mkdir($path, 0755);

        // полный путь к выгружаемому файлу
        $rn = 'UtilizationCertificate_' . (!empty($model->fo_project) ? 'p' . $model->fo_project : (!empty($model->fo_customer) ? 'ca' . $model->fo_customer : 'id' . $model->id)) . '.docx';
        $r = $path . $rn; // для сохранения на диске
        $ffp = Yii::getAlias('@uploads-documents-fs') . $rn; // full file path, для отдачи пользователю
        $ffp = str_replace('\\', '/', $ffp);

        // имя шаблона определяется в соответствии с ОГРН выбранной организации
        $tmplName = '';
        if (!empty($model->organization) && !empty($model->organization->ogrn)) {
            $tmplName = 'tmpl_UtilCert_' . $model->organization->ogrn . '.docx';
        }

        if (!empty($tmplName)) {
            // массив с данными для замены
            $arraySubst = [];

            $arraySubst['%UC_DATE_REP%'] = '«' . Yii::$app->formatter->asDate($model->act_date, 'php:d') . '» ' . Yii::$app->formatter->asDate($model->act_date, 'php:F Y года');

            $director = '';
            if (!empty($model->org_id)) {
                $director = $model->organization->dir_name_short;
            }
            $arraySubst['%DIRECTOR_NAME_SHORT%'] = $director;
            unset($director);

            $ed = $model->ed;
            $edRep = 'договора';
            $director = '';
            if (!empty($ed)) {
                $edRep .= ' № ' . $ed->doc_num . ' от ' . Yii::$app->formatter->asDate($ed->doc_date, 'php:d.m.Y г.');
                $director = $ed->req_name_full;
            }
            else {
                $director = $model->counteragentName;
            }
            $arraySubst['%ED_REP%'] = $edRep;
            $arraySubst['%ED_CA_NAME%'] = $director;
            unset($director);

            // табличная часть
            $iterator = 1;
            $tp = $model->documentsTps;
            foreach ($tp as $row) {
                $arraySubst['%TP_NAME_' . $iterator . '%'] = $row->name;
                $arraySubst['%TP_FKKO_' . $iterator . '%'] = $row->fkkoName;
                $arraySubst['%TP_DC_' . $iterator . '%'] = $row->dcName;
                $arraySubst['%TP_UNIT_' . $iterator . '%'] = $row->unitName;
                $arraySubst['%TP_QUANTITY_' . $iterator . '%'] = $row->quantity;
                $arraySubst['%TP_HK_' . $iterator . '%'] = $row->hkName;

                $iterator++;
            }

            if (count($tp) > 0) {
                // очистим от переменных оставшиеся строки шаблона, всего их там 10 (на момент разработки)
                // часть уже заполнена, остальные очищаем
                if (count($tp) <= self::TEMPLATE_APP_MAX_ROWS_COUNT) {
                    for ($i = $iterator; $i <= self::TEMPLATE_APP_MAX_ROWS_COUNT; $i++) {
                        $arraySubst['%TP_NAME_' . $i . '%'] = '';
                        $arraySubst['%TP_FKKO_' . $i . '%'] = '';
                        $arraySubst['%TP_DC_' . $i . '%'] = '';
                        $arraySubst['%TP_UNIT_' . $i . '%'] = '';
                        $arraySubst['%TP_QUANTITY_' . $i . '%'] = '';
                        $arraySubst['%TP_HK_' . $i . '%'] = '';
                    }
                }
            }

            // виды обращения с отходами
            $hks = $model->documentsHksIdsArray;
            for ($i = 1; $i < 10; $i++) {
                if (in_array($i, $hks))
                    $arraySubst['%HK_' . $i . '%'] = 'x';
                else
                    $arraySubst['%HK_' . $i . '%'] = '';
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

    /**
     * Делает выборку табличных частей документов, выписанных к проекту, переданному в параметрах.
     * @param $project_id
     * @return array
     */
    private function fetchDocumentTp($project_id)
    {
        $documentsTableName = foListDocuments::tableName();

        return foListDocumentsTp::find()
            ->select([
                'name' => '[TOVAR_DOC]',
                'dc' => '[LIST_TOVAR].[ADD_klass_opasnosti]',
                'quantity' => foListDocumentsTp::tableName() . '.[KOL_VO]',
                'unitName' => foListDocumentsTp::tableName() . '.[ED_IZM_TOVAR]',
            ])
            ->leftJoin($documentsTableName, $documentsTableName . '.[ID_DOC] = ' . foListDocumentsTp::tableName() . '.[ID_DOC]')
            ->leftJoin('[CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR]', '[LIST_TOVAR].[ID_TOVAR] = [LIST_TOVAR_DOC].[ID_TOVAR]')
            // документы по выбранному проекту
            ->where(['ID_LIST_PROJECT_COMPANY' => $project_id])
            // только счета, приложения всякие дублируют табличную часть
            ->andWhere([$documentsTableName . '.[ID_TIP_DOC]' => foListDocuments::НАБОР_ТИПОВ_ДОКУМЕНТОВ_ПРИЗНАВАЕМЫХ_СЧЕТАМИ])
            // выбрасываем транспортные услуги, они в акте не нужны
            ->andWhere('[TOVAR_DOC] NOT LIKE \'%спортные услуги%\'')
            // счет не должен быть удаленным
            ->andWhere([
                'or',
                [$documentsTableName . '.TRASH' => null],
                [$documentsTableName . '.TRASH' => 0],
            ])
            ->asArray()->all();
    }

    /**
     * Выполняет заполнение шаблона и отдает готовый файл пользоватедю.
     * @param $outputName string имя выходного файла
     * @param $tmplName string имя файла исходного шаблона, на основании которого будет генерироваться документ
     * @param $arraySubst array массив с данными для подстановки
     * @throws NotFoundHttpException
     */
    private function generateByTemplate($outputName, $tmplName, $arraySubst)
    {
        // папка с шаблонами
        $tmplDir = Yii::getAlias('@uploads-export-templates-fs');

        if (!is_file($tmplDir . $tmplName)) {
            Yii::$app->session->setFlash('error', 'Файл шаблона не обнаружен по указанному пути! Документ не может сгенерирован.');
            return;
        }

        // папка для выгрузки
        $path = Yii::getAlias('@uploads-documents-fs');
        if (!file_exists($path) && !is_dir($path)) mkdir($path, 0755);

        // полный путь к выгружаемому файлу
        $r = $path . $outputName; // для сохранения на диске
        $ffp = Yii::getAlias('@uploads-documents-fs') . $outputName; // full file path, для отдачи пользователю
        $ffp = str_replace('\\', '/', $ffp);

        $docx_gen = new \DocXGen;
        $docx_gen->docxTemplate($tmplDir . $tmplName, $arraySubst, $r);

        if (preg_match('/^[a-z0-9]+\.[a-z0-9]+$/i', $outputName) !== 0 || !is_file("$ffp")) {
            throw new NotFoundHttpException('Запрошенный файл не существует.');
        }

        \Yii::$app->response->sendFile($ffp, $outputName, ['mimeType' => 'application/docx']);
        if (file_exists($ffp)) unlink($ffp);
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionGenerateTtn()
    {
        $model = new DocumentTtnGenerationForm();

        if ($model->load(Yii::$app->request->post())) {
            $project = foProjects::findOne($model->project_id);
            if ($project && in_array($project->ID_LIST_SPR_PROJECT, ProjectsTypes::НАБОР_ВЫВОЗ_ЗАКАЗЫ)) {
                $organization = $model->getOrganization();

                // попробуем идентифицировать транспортное средство
                $transport = '';
                $transportRn = '';
                $driver = '';
                if (!empty($project->ADD_dannie)) {
                    $ferryman = Ferrymen::findOne(['name_crm' => $project->ADD_perevoz]);
                    if ($ferryman) {
                        $data = str_replace(chr(32), '', mb_strtolower($project->ADD_dannie));
                        $data = str_replace('/', '', $data);
                        $data = str_replace('\\', '', $data);

                        // перевозчик идентифицирован, теперь попробуем найти у него такой транспорт
                        foreach ($ferryman->transport as $record) {
                            if (false !== stripos($data, $record->rn_index)) {
                                // транспорт такой наден, зафиксируем это
                                $transport = (!empty($record->ttName) ? $record->ttName . ' ' : '') . $record->brandName;
                                $transportRn = $record->rn . (!empty($record->trailer_rn) ? ', прицеп г/н ' . $record->trailer_rn : '');
                                break;
                            }
                        }

                        // найдем водителя
                        foreach ($ferryman->drivers as $record) {
                            $driverName = mb_strtolower(trim($record->surname) . trim($record->name));
                            $driverSurname = mb_strtolower(trim($record->surname) . trim($record->name) . trim($record->patronymic));
                            if (false !== stripos($data, $record->driver_license_index) || false !== stripos($data, $driverName) || false !== stripos($data, $driverSurname)) {
                                // водитель такой наден, зафиксируем это
                                $driver = trim(implode(' ', [
                                    $record->surname,
                                    $record->name,
                                    $record->patronymic,
                                ]));
                                break;
                            }
                        }
                    }
                }

                $arraySubst = [
                    '{%DOC_DATE%}' => Yii::$app->formatter->asDate($model->date, 'php:d.m.Y г.'),
                    '{%TRANSPORT%}' => $transport,
                    '{%TRANSPORT_RN%}' => $transportRn,
                    '{%CA_NAME%}' => $model->ca_name,
                    '{%ORG_NAME%}' => $organization->name_short,
                    '{%CA_CONTACT_PERSON%}' => $model->ca_contact_person,
                    '{%ADDRESS_LOAD%}' => $model->ca_address,
                    '{%ADDRESS_UNLOAD%}' => $organization->address_ttn,
                    '{%DRIVER_NAME%}' => $driver,
                    '{%PROJECT_NUMBER%}' => $model->project_id,
                ];

                $tp = [];

                switch ($project->ID_LIST_SPR_PROJECT) {
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                        // для заказов данные извлекаются из табличных частей документов, связанных с проектом

                        $tp = $this->fetchDocumentTp($model->project_id);

                        $names = '';
                        $units = '';
                        foreach ($tp as $row) {
                            $names .= $row['name'] . ' ';
                            $units .= $row['quantity'] . ' ' . $row['unitName'] . ' ';
                        }

                        $names = trim($names, ' ');
                        $units = trim($units, ' ');

                        $arraySubst['{%TP_NAME%}'] = $names;
                        $arraySubst['{%TP_PACKING%}'] = '';
                        $arraySubst['{%TP_QUANTITY_UNIT%}'] = $units;

                        break;
                    case ProjectsTypes::PROJECT_TYPE_ВЫВОЗ:
                        // для вывоза данные извлекаются из параметров проекта
                        $tp[] = [1];

                        $parameters = foProjectsParameters::find()->select([
                            'name' => '[PROPERTIES_PROGECT]',
                            'value' => '[VALUES_PROPERTIES_PROGECT]',
                        ])->where(['ID_LIST_PROJECT_COMPANY' => $model->project_id])->asArray()->all();

                        $name = '';
                        $key = array_search('Отход', array_column($parameters, 'name'));
                        if (false !== $key) $name = $parameters[$key]['value'];

                        $packing = '';
                        $key = array_search('Упаковка отхода', array_column($parameters, 'name'));
                        if (false !== $key) $packing = $parameters[$key]['value'];

                        $quantity = '';
                        $key = array_search('Вес и объем', array_column($parameters, 'name'));
                        if (false !== $key) $quantity = $parameters[$key]['value'];

                        $arraySubst['{%TP_NAME%}'] = $name;
                        $arraySubst['{%TP_PACKING%}'] = $packing;
                        $arraySubst['{%TP_QUANTITY_UNIT%}'] = $quantity;

                        break;
                }

                $this->generateByTemplate('ttn_' . $model->project_id . '.docx', 'tmpl_TTN.docx', $arraySubst);
            }
        }
        else {
            $model->date = date('Y-m-d', time());

            // извлекаем идентификатор проекта из сессии
            $key = 'id_for_generating_ttn_' . Yii::$app->user->id;
            $id = Yii::$app->session->get($key);
            if (!empty($id)) {
                $model->project_id = $id;
                Yii::$app->session->remove($key);

                // проект идентифицирован, попробуем выяснить заказчика и сопутствующую информацию
                $project = foProjects::findOne($model->project_id);
                if ($project) {
                    $model->ca_name = $project->companyName;
                    // закомментировано по просьбе Заказчика
                    //$model->ca_contact_person = $project->contactPersonName;
                    $model->ca_address = $project->paramAddressValue; // адрес из параметров проекта
                }

                // адрес и полное наименование контрагента определим через сервис
                if (!empty($project->companyInn)) {
                    $details = DadataAPI::postRequestToApi($project->companyInn);
                    if (false !== $details) {
                        if (isset($details['address']['unrestricted_value'])) {
                            $model->ca_address = $details['address']['unrestricted_value'];
                        }

                        // а вдруг наименование уже изменилось, пока мы спали?
                        if (isset($details['name']['short_with_opf'])) {
                            $model->ca_name = $details['name']['short_with_opf'];
                        }
                    }
                }
            }
        }

        return $this->render('generate_ttn', ['model' => $model]);
    }

    public function actionGenerateApp()
    {
        $model = new DocumentAPPGenerationForm();

        if ($model->load(Yii::$app->request->post())) {
            $project = foProjects::findOne($model->project_id);
            if ($project && in_array($project->ID_LIST_SPR_PROJECT, ProjectsTypes::НАБОР_ВЫВОЗ_ЗАКАЗЫ)) {
                // попробуем идентифицировать транспортное средство
                $transport = '';
                $transportRn = '';
                if (!empty($project->ADD_dannie)) {
                    $ferryman = Ferrymen::findOne(['name_crm' => $project->ADD_perevoz]);
                    if ($ferryman) {
                        $data = str_replace(chr(32), '', mb_strtolower($project->ADD_dannie));
                        $data = str_replace('/', '', $data);
                        $data = str_replace('\\', '', $data);

                        // перевозчик идентифицирован, теперь попробуем найти у него такой транспорт
                        foreach ($ferryman->transport as $record) {
                            if (false !== stripos($data, $record->rn_index)) {
                                // транспорт такой наден, зафиксируем это
                                $transport = (!empty($record->ttName) ? $record->ttName . ' ' : '') . $record->brandName;
                                $transportRn = $record->rn . (!empty($record->trailer_rn) ? ', прицеп г/н ' . $record->trailer_rn : '');
                                break;
                            }
                        }
                    }
                }

                $arraySubst = [
                    '{%DOC_DATE%}' => Yii::$app->formatter->asDate($model->date, 'php:d.m.Y г.'),
                    '{%TRANSPORT%}' => $transport,
                    '{%TRANSPORT_RN%}' => $transportRn,
                ];

                $tp = [];
                $iterator = -1;

                switch ($project->ID_LIST_SPR_PROJECT) {
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                        // для заказов данные извлекаются из табличных частей документов, связанных с проектом
                        $tp = $this->fetchDocumentTp($model->project_id);

                        $iterator = 1;
                        foreach ($tp as $row) {
                            $arraySubst['{%TP_NAME_' . $iterator . '%}'] = $row['name'];
                            $arraySubst['{%TP_DC_' . $iterator . '%}'] = $row['dc'];
                            $arraySubst['{%TP_UNIT_' . $iterator . '%}'] = $row['unitName'];
                            $arraySubst['{%TP_QUANTITY_' . $iterator . '%}'] = $row['quantity'];

                            $iterator++;
                        }

                        break;
                    case ProjectsTypes::PROJECT_TYPE_ВЫВОЗ:
                        // для вывоза данные извлекаются из параметров проекта
                        $tp[] = [1];
                        $iterator = 2;

                        $parameters = foProjectsParameters::find()->select([
                            'name' => '[PROPERTIES_PROGECT]',
                            'value' => '[VALUES_PROPERTIES_PROGECT]',
                        ])->where(['ID_LIST_PROJECT_COMPANY' => $model->project_id])->asArray()->all();

                        $name = '';
                        $key = array_search('Отход', array_column($parameters, 'name'));
                        if (false !== $key) $name = $parameters[$key]['value'];

                        $unit = '';
                        $key = array_search('Упаковка отхода', array_column($parameters, 'name'));
                        if (false !== $key) $unit = $parameters[$key]['value'];

                        $quantity = '';
                        $key = array_search('Вес и объем', array_column($parameters, 'name'));
                        if (false !== $key) $quantity = $parameters[$key]['value'];

                        $arraySubst['{%TP_NAME_1%}'] = $name;
                        $arraySubst['{%TP_DC_1%}'] = '';
                        $arraySubst['{%TP_UNIT_1%}'] = $unit;
                        $arraySubst['{%TP_QUANTITY_1%}'] = $quantity;

                        break;
                }

                if (count($tp) > 0) {
                    // очистим от переменных оставшиеся строки шаблона, всего их там 10 (на момент разработки)
                    // часть уже заполнена, остальные очищаем
                    if (count($tp) <= self::TEMPLATE_APP_MAX_ROWS_COUNT) {
                        for ($i = $iterator; $i <= self::TEMPLATE_APP_MAX_ROWS_COUNT; $i++) {
                            $arraySubst['{%TP_NAME_' . $i . '%}'] = '';
                            $arraySubst['{%TP_DC_' . $i . '%}'] = '';
                            $arraySubst['{%TP_UNIT_' . $i . '%}'] = '';
                            $arraySubst['{%TP_QUANTITY_' . $i . '%}'] = '';
                        }
                    }

                    $this->generateByTemplate('app_' . $model->project_id . '.docx', 'tmpl_APP.docx', $arraySubst);
                }
            }
        }
        else {
            $model->date = date('Y-m-d', time());
            // извлекаем идентификатор проекта из сессии
            $key = 'id_for_generating_app_' . Yii::$app->user->id;
            $id = Yii::$app->session->get($key);
            if (!empty($id)) {
                $model->project_id = $id;
                Yii::$app->session->remove($key);
            }
        }

        return $this->render('generate_app', ['model' => $model]);
    }
}
