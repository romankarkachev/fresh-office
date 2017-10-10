<?php

namespace backend\controllers;

use Yii;
use common\models\ProductionFeedbackFiles;
use common\models\ProductionFeedbackFilesSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ProductionFeedbackFilesController implements the CRUD actions for ProductionFeedbackFiles model.
 */
class ProductionFeedbackFilesController extends Controller
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
                        'actions' => ['list', 'delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    [
                        'actions' => ['index', 'download-file'],
                        'allow' => true,
                        'roles' => ['root', 'prod_feedback'],
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
     * Отображает список файлов в привычном табличном виде.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new ProductionFeedbackFilesSearch();
        $dataProvider = $searchModel->search(ArrayHelper::merge(
            Yii::$app->request->queryParams,
            [
                'route' => 'production-feedback-files/list',
                'pageSize' => 50,
            ]
        ));

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Выводит файлы обратной связи от производства в виде плитки с визуальной группировкой по проектам.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductionFeedbackFilesSearch();

        // проверим, применяется ли отбор
        // если все параметры отбора не заполнены одновременно, то отчет не будем показывать
        $paramsCountEmpty = 0;
        $searchApplied = true;
        if (Yii::$app->request->get($searchModel->formName()) != null) {
            foreach (Yii::$app->request->get($searchModel->formName()) as $param) {
                if ($param == null) {
                    $paramsCountEmpty++;
                }
            }

            if ($paramsCountEmpty == count(Yii::$app->request->get($searchModel->formName()))) $searchApplied = false;
        }
        else
            $searchApplied = false;

        // если нет отбора за период, то отчет не показываем совсем
        if (!$searchApplied) {
            // можно и вернуть значение по-умолчанию:
            return $this->render('_index_nodata', [
                'searchModel' => $searchModel,
            ]);
        }

        $dataProvider = $searchModel->search(ArrayHelper::merge(
            Yii::$app->request->queryParams,
            [
                'route' => 'production-feedback-files',
                'pageSize' => 100,
            ]
        ));

        if (Yii::$app->request->get('downloadArchive') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет файлов для помещения в архив.');
                return $this->redirect(['/production-feedback-files']);
            }

            $filepath = ProductionFeedbackFiles::getUploadsFilepath();
            if (false == $filepath) {
                Yii::$app->getSession()->setFlash('error', 'Не удалось создать папку для хранения временных файлов.');
                return $this->redirect(['/production-feedback-files']);
            }

            $temp_fn = Yii::$app->security->generateRandomString(8) . '.zip';
            $temp_ffp = $filepath . '/' . $temp_fn;

            // файлы изображений есть, создаем архив
            try {
                // выполняем попытку создания архива с пакетом документов
                $zip = new \ZipArchive();
                $zip->open($temp_ffp, \ZipArchive::CREATE);

                // добавляем документы, входящие в пакет
                foreach ($dataProvider->getModels() as $image) {
                    /* @var $image \common\models\ProductionFeedbackFiles */

                    // проверяем физическое существование файла
                    if (file_exists($image->ffp)) {
                        // файл существует, копируем его во временный файл, в котором будут производиться изменения
                        $zip->addFile($image->ffp, $image->project_id . '-' . $image->ofn);
                    }
                }

                $zip->close();
                \Yii::$app->response->sendFile($temp_ffp, 'production_files-' . $temp_fn, ['mimeType'=>'application/zip']);
                if (file_exists($temp_ffp)) unlink($temp_ffp);
            }
            catch (\Exception $exception) {
                // если сюда не свалилось, значит процедура может идти дальше
                // а если свалилось, чистим следы выполнения
                // удаляем временный файл
                if (file_exists($temp_ffp)) unlink($temp_ffp);

                Yii::$app->getSession()->setFlash('error', $exception->getMessage()."<br>" . $exception->getTraceAsString());

                return $this->redirect(['/production-feedback-files']);
            }
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchApplied' => $searchApplied,
                'queryString' => $queryString,
            ]);
        }
    }

    /**
     * Deletes an existing ProductionFeedbackFiles model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the ProductionFeedbackFiles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductionFeedbackFiles the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductionFeedbackFiles::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
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
            $model = ProductionFeedbackFiles::findOne($id);
            if (file_exists($model->ffp))
                return Yii::$app->response->sendFile($model->ffp, $model->ofn);
            else
                throw new NotFoundHttpException('Файл не обнаружен.');
        };
    }
}
