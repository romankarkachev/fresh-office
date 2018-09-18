<?php

namespace cemail\controllers;

use common\models\CEMailboxesTypes;
use Yii;
use common\models\CEMailboxes;
use common\models\CEMailboxesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * MailboxesController implements the CRUD actions for CEMailboxes model.
 */
class MailboxesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete', 'toggle-activity'],
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
                ],
            ],
        ];
    }

    /**
     * Lists all CEMailboxes models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CEMailboxesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CEMailboxes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CEMailboxes();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(['/mailboxes']);
        } else {
            $model->is_active = true;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CEMailboxes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/mailboxes']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CEMailboxes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $details = [
            'details' => [
                'modelRep' => $model->name,
                'breadcrumbs' => ['label' => 'Почтовые ящики', 'url' => ['/mailboxes']],
                'buttonCaption' => 'Почтовые ящики',
                'buttonUrl' => ['/mailboxes'],
                'action1' => 'удалить',
                'action2' => 'удален',
            ],
        ];

        // запись не должна где-то быть использован
        if ($model->checkIfUsed()) return $this->render('@backend/views/common/cannot_delete', $details);

        $model->delete();

        return $this->redirect(['/mailboxes']);
    }

    /**
     * Finds the CEMailboxes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CEMailboxes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CEMailboxes::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Переключает возможность сбора писем из почтового ящика на лету.
     * @return bool
     */
    public function actionToggleActivity()
    {
        $id = intval(Yii::$app->request->post('id'));
        $model = CEMailboxes::findOne($id);
        if ($model) {
            if ($model->is_active)
                $model->is_active = false;
            else
                $model->is_active = true;
            return $model->save(false);
        }

        return false;
    }
}
