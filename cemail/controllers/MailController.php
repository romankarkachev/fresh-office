<?php

namespace cemail\controllers;

use Yii;
use common\models\CEMessages;
use common\models\CEMessagesSearch;
use common\models\CEAddresses;
use common\models\CEAttachedFiles;
use common\models\CEMailboxes;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * MailController implements the CRUD actions for CEMessages model.
 */
class MailController extends Controller
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
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'delete', 'clear', 'flush'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'clear' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CEMessages models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CEMessagesSearch();
        $dataProvider = $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, [
            $searchModel->formName() => ['is_complete' => true],
        ]));

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
            'mailboxes' => CEMailboxes::find()->all(),
        ]);
    }

    /**
     * Creates a new CEMessages model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CEMessages();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/mail']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CEMessages model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CEMessages model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/mail']);
    }

    /**
     * Finds the CEMessages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CEMessages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CEMessages::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Выполняет удаление всех счетов из обеих баз.
     */
    public function actionClear()
    {
        // удаляем адреса к письмам
        CEAddresses::deleteAll();

        // удаляем аттачи к файлам
        CEAttachedFiles::deleteAll();

        // удаляем письма
        CEMessages::deleteAll();

        return $this->redirect(['/mail']);
    }

    /**
     * Выполняет выгрузку всех закомпличенных писем в текстовый файл.
     */
    public function actionFlush()
    {
        $filepath = Yii::getAlias('@uploads-mail-extractions-fs');
        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath, 0775, true)) return false;
        }

        $fn = '/extraction-' . date('Y-m-d-H-i') . '.txt';
        $ffp = realpath($filepath) . $fn;
        $fp = fopen($ffp, 'w+');
        $bodies = CEMessages::find()->where(['is_complete' => true])->one(); // one вместо all, чтобы не сильно долго
        foreach ($bodies as $body) {
            /* @var $body CEMessages */
            file_put_contents($ffp, $body->body_text . PHP_EOL, FILE_APPEND);
        }

        fclose($fp);
    }
}
