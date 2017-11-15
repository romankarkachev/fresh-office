<?php

namespace backend\controllers;

use Yii;
use common\models\LicensesRequests;
use common\models\LicensesRequestsSearch;
use common\models\LicensesRequestsStates;
use common\models\LicensesRequestsFkko;
use common\models\LicensesRequestsFkkoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;

/**
 * LicensesRequestsController implements the CRUD actions for LicensesRequests model.
 */
class LicensesRequestsController extends Controller
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
                        'actions' => ['index', 'update'],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_head'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        'actions' => ['create', 'fkko-list'],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_head', 'sales_department_manager'],
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
     * Делает выборку кодов ФККО, которые относятся к этому запросу и возвращает dataProvider.
     * @param $id integer идентификатор запроса лицензии
     * @return \yii\data\ActiveDataProvider
     */
    private function fkkoDataProvider($id)
    {
        $searchModel = new LicensesRequestsFkkoSearch();
        return $searchModel->search([
            $searchModel->formName() => [
                'lr_id' => $id,
            ],
        ]);
    }

    /**
     * Формирует PDF из файлов сканов лицензии и возвращает путь к сформированному файлу.
     * @param $lr_id integer идентификатор запроса
     * @return mixed
     */
    private function generatePdf($lr_id)
    {
        $files = LicensesRequestsFkko::find()
            ->groupBy('file_id')
            ->where(['lr_id' => $lr_id])
            ->all();

        $content = '';
        foreach ($files as $file) {
            $content .= $this->renderPartial('@common/mail/_licensesScans', ['file' => $file, 'lr_id' => $lr_id]);
        }

        $filepath = Yii::getAlias('@uploads-temp-pdfs');
        $filename = $filepath . '/lr-' . $lr_id . '.pdf';
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'destination' => Pdf::DEST_FILE,
            'filename' => $filename,
            'content' => $content,
            // поля
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 10,
            'marginBottom' => 10,
        ]);

        try {
            $pdf->render();
            return $filename;
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Lists all LicensesRequests models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LicensesRequestsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Рендерит только табличную часть запроса лицензии.
     * В принципе для pjax только и используется.
     * @param $id integer идентификатор запроса, таблична часть которого будет извлекаться
     * @return mixed
     */
    public function actionFkkoList($id)
    {
        return $this->render('_fkko', [
            'dataProvider' => $this->fkkoDataProvider($id),
        ]);
    }

    /**
     * Creates a new LicensesRequests model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new LicensesRequests();
        $model->state_id = LicensesRequestsStates::LICENSE_STATE_НОВЫЙ;

        if ($model->load(Yii::$app->request->post())) {
            // если файл по сформированному пути удалось загрузить на сервер и успешно на нем сохранить
            if ($model->validate() && $model->save()) {
                // если удалось сохранить модель, то создадим необходимое количество записей кодов ФККО
                foreach ($model->tpFkkos as $fkko) {
                    /* @var $fkko LicensesRequestsFkko */
                    $fkko->lr_id = $model->id;
                    $fkko->save();
                }

                return $this->redirect(['/licenses-requests']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing LicensesRequests model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $state = $model->state_id;
            if (Yii::$app->request->post('reject') !== null) {
                $model->state_id = LicensesRequestsStates::LICENSE_STATE_ОТКАЗ;
                $body = 'По Вашему запросу лицензии был получен отказ. Причина:<br />' . $model->comment;
            }
            else if (Yii::$app->request->post('allow') !== null) {
                $model->state_id = LicensesRequestsStates::LICENSE_STATE_ОДОБРЕН;
                $body = 'Ваш запрос лицензии был одобрен, файл с необходимыми сканами находится во вложении.';
            }

            if ($model->save(true, ['state_id', 'comment'])) {
                // формируем сам файл с вложенными в него сканами лицензий
                $pdfFfp = $this->generatePdf($id);

                $letter = Yii::$app->mailer->compose([
                    'html' => 'licenseRequest-html',
                ], [
                    'body' => $body,
                ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameSvetozar']]);

                $letter->setTo($model->createdByEmail)
                    ->setSubject('Сканы лицензии по запросу № ' . $model->id . ' от ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y H:i'));

                $letter->attach($pdfFfp);

                if ($letter->send()) {
                    if (file_exists($pdfFfp)) unlink($pdfFfp);

                    return $this->redirect(['/licenses-requests']);
                }
                if (file_exists($pdfFfp)) unlink($pdfFfp);
            }
            else $model->state_id = $state;
        }

        return $this->render('update', [
            'model' => $model,
            // табличная часть запроса (коды ФККО)
            'dataProvider' => $this->fkkoDataProvider($id),
        ]);
    }

    /**
     * Deletes an existing LicensesRequests model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/licenses-requests']);
    }

    /**
     * Finds the LicensesRequests model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return LicensesRequests the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LicensesRequests::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
