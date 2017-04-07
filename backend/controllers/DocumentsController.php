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
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete', 'render-row', 'delete-row', 'api-get-project-data', 'api-get-project-table-part'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root', 'role_documents'],
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
                    foreach ($dtp as $tp) {
                        $row = new DocumentsTp();
                        $row->attributes = $tp->attributes;
                        $row->save();
                    }

                    // очищаем отметки с видами обращения и записываем заново
                    DocumentsHk::deleteAll(['doc_id' => $model->id]);
                    foreach ($dhk as $hk) {
                        $row = new DocumentsHk();
                        $row->attributes = $hk->attributes;
                        $row->save();
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
        DocumentsTp::deleteAll(['doc_id' => $id]);
        DocumentsHk::deleteAll(['doc_id' => $id]);

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
        $document = Documents::findOne($doc_id);
        if ($document == null) return $this->redirect(['/documents']);

        // папка с шаблонами
        $tmpldir = Yii::getAlias('@uploads-export-templates-fs');

        // папка для выгрузки
        $path = Yii::getAlias('@uploads-documents-fs');
        if (!file_exists($path) && !is_dir($path)) mkdir($path, 0755);
        //$exportdir = realpath($path);

        // количество строк табличной части
        $tp_count = $document->documentsTpsCount;
        if ($tp_count == 0) $tp_count = ''; else $tp_count = '_' . $tp_count;

        // полный путь к шаблону
        $f = $tmpldir.'template' . $tp_count . '.docx';

        // полный путь к выгружаемому файлу
        $rn = $document->fo_project . '_' . $document->fo_customer . '.docx';
        $r = $path . $rn; // для сохранения на диске
        $ffp = Yii::getAlias('@uploads-documents-fs') . $rn; // full file path, для отдачи пользователю
        $ffp = str_replace('\\', '/', $ffp);

        // массив с данными для замены
        $array_subst = [];

        $array_subst['%DOC_NUM%'] = $document->doc_num;
        $array_subst['%DOC_DATE%'] = Yii::$app->formatter->asDate($document->doc_date, 'php:d.m.Y');
        $array_subst['%PROJECT_ID%'] = $document->fo_project;
        $array_subst['%CONTRACT_ID%'] = $document->fo_contract;

        // табличная часть
        $iterator = 1;
        foreach ($document->documentsTps as $row) {
            $array_subst['%TP_NAME_' . $iterator . '%'] = $row->product->name;
            $array_subst['%TP_DC_' . $iterator . '%'] = $row->dc;
            $array_subst['%TP_UNIT_' . $iterator . '%'] = $row->product->unit;
            $array_subst['%TP_QUANTITY_' . $iterator . '%'] = $row->quantity;

            $iterator++;
        }

        // виды обращения с отходами
        $hks = $document->documentsHksIdsArray;
        for ($i = 1; $i < 10; $i++) {
            if (in_array($i, $hks))
                $array_subst['%HK_' . $i . '%'] = 'x';
            else
                $array_subst['%HK_' . $i . '%'] = '';
        }

        $docx_gen = new \DocXGen;
        $docx_gen->docxTemplate($f, $array_subst, $r);

        if (preg_match('/^[a-z0-9]+\.[a-z0-9]+$/i', $rn) !== 0 || !is_file("$ffp")) {
            throw new NotFoundHttpException('Запрошенный файл не существует.');
        }

        return Yii::$app->response->sendFile($ffp, $rn);
    }
}
