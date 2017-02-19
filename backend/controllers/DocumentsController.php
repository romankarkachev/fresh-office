<?php

namespace backend\controllers;

use Yii;
use common\models\Documents;
use common\models\DocumentsSearch;
use common\models\DocumentsHk;
use common\models\DocumentsTp;
use common\components\DocXGen;
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
                'only' => ['index', 'create', 'update', 'delete', 'render-row', 'delete-row'],
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/documents']);
        } else {
            // табличная часть
            $tp = DocumentsTp::find()->where(['doc_id' => $model->id])->all();
            if ($tp == null) $tp[] = new DocumentsTp();

            // виды обращения с отходами
            $hk = DocumentsHk::find()->where(['doc_id' => $model->id])->all();
            if ($hk == null) $hk[] = new DocumentsHk();

            return $this->render('update', [
                'model' => $model,
                'tprows' => $tp,
                'hks' => $hk,
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

        // полный путь к шаблону
        $f = $tmpldir.'/template.docx';

        // полный путь к выгружаемому файлу
        $rn = $document->fo_project.'_'.$document->fo_customer.'.docx';
        $r = $exportdir.'/'.$rn;

        // массив с данными для замены
        $array_subst = [];

        $array_subst['%DOC_NUM%'] = $document->id;
        $array_subst['%DOC_DATE%'] = Yii::$app->formatter->asDate($document->doc_date, 'php:d.m.Y');
        $array_subst['%PROJECT_ID%'] = $document->fo_project;
        $array_subst['%CONTRACT_ID%'] = $document->fo_contract;

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
