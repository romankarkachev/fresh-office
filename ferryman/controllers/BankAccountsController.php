<?php

namespace ferryman\controllers;

use Yii;
use common\models\FerrymenBankDetails;
use common\models\FerrymenBankDetailsSearch;
use common\models\Ferrymen;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * BankAccountsController implements the CRUD actions for FerrymenBankDetails model.
 */
class BankAccountsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root', 'ferryman'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all FerrymenBankDetails models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FerrymenBankDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'bank-accounts');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new FerrymenBankDetails model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FerrymenBankDetails();

        if ($model->load(Yii::$app->request->post())) {
            // если запрос выполняет перевозчик, то заполним поле "Перевозчик" текущей модели
            if (Yii::$app->user->can('ferryman')) {
                $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
                // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
                if ($ferryman != null) {
                    $model->ferryman_id = $ferryman->id;
                    if ($model->save()) return $this->redirect(['/bank-accounts']);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing FerrymenBankDetails model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/bank-accounts']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing FerrymenBankDetails model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/bank-accounts']);
    }

    /**
     * Finds the FerrymenBankDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FerrymenBankDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FerrymenBankDetails::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }
}
