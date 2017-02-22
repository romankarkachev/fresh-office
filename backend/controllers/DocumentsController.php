<?php

namespace backend\controllers;

use common\models\HandlingKinds;
use common\models\Products;
use common\models\ProductsExcludes;
use common\models\ProductsImport;
use Yii;
use common\models\Documents;
use common\models\DocumentsSearch;
use common\models\DocumentsHk;
use common\models\DocumentsTp;
use common\components\DocXGen;
use yii\bootstrap\ActiveForm;
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
                        'roles' => ['root'],
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
     * Выполняет запрос данных проекта через API FreshOffice.
     * Возвращает массив проектов, соответствующих значению в параметрах.
     * @param $project_id
     * @return array
     */
    public function actionApiGetProjectData($project_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fo_project = json_decode(Documents::makeGetRequestToApi('project(' . $project_id . ')', 'id_list_project_company,id_company,date_create_project'), true);
        if (isset($fo_project['d'])) {
            $fo_project = $fo_project['d'];
            $company = json_decode(Documents::makeGetRequestToApi('companies(' . $fo_project['id_company'] . ')', 'id,name'), true);
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

        $fo_projects = json_decode(Documents::makeGetRequestToApi('tovar_project', null, 'id_list_project_company eq ' . $project_id), true);
        //var_dump($fo_projects); return;
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
                //var_dump($project); continue;
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
     * @return mixed
     */
    public function actionExport($doc_id)
    {
        $document = Documents::findOne($doc_id);
        if ($document == null) return $this->redirect(['/documents/update', 'id' => $doc_id]);

        // папка с шаблонами
        $tmpldir = realpath(__DIR__.'/../../uploads/export-templates');

        // папка для выгрузки
        $path = __DIR__.'/../../uploads/documents';
        if (!file_exists($path) && !is_dir($path)) mkdir($path, 0755);
        $exportdir = realpath($path);

        // количество строк табличной части
        $tp_count = $document->documentsTpsCount;
        if ($tp_count == 0) $tp_count = ''; else $tp_count = '_' . $tp_count;

        // полный путь к шаблону
        $f = $tmpldir.'/template' . $tp_count . '.docx';

        // полный путь к выгружаемому файлу
        $rn = $document->fo_project.'_'.$document->fo_customer.'.docx';
        $r = $exportdir.'/'.$rn;

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

        DocXGen::docxTemplate($f, $array_subst, $r);
        return Yii::$app->response->sendFile($r, $rn);
        //%DOC_NUM%
        //%DOC_DATE%
        //%PROJECT_ID%
        //%CONTRACT_ID%

        //%TP_NUM%
        //%TP_NAME%
        //%TP_DC%
        //%TP_UNIT%
        //%TP_QUANTITY%

        //%HK_GATHERING%
        //%HK_TRANSPORTATION%
        //%HK_PROCESSING%
        //%HK_UTILIZATION%
        //%HK_NEUTRALIZATION%
        //%HK_PLACEMENT%
    }
}
