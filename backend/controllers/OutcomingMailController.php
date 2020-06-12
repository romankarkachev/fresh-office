<?php

namespace backend\controllers;

use Yii;
use common\models\IncomingMail;
use common\models\OutcomingMailSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OutcomingMailController implements the CRUD actions for IncomingMail model.
 */
class OutcomingMailController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = 'outcoming-mail';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Исходящая корреспонденция';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    /**
     * URL для рендеринга поля со входящим номером
     */
    const URL_RENDER_FIELD_INC_NUM = 'render-field-outc-num';
    const URL_RENDER_FIELD_INC_NUM_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_RENDER_FIELD_INC_NUM];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'create', self::URL_UPDATE, self::URL_UPDATE, self::URL_RENDER_FIELD_INC_NUM],
                        'allow' => true,
                        'roles' => ['root', 'assistant'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all IncomingMail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OutcomingMailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new IncomingMail model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new IncomingMail();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
            }
        }
        else {
            $model->inc_date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
            $model->direction = IncomingMail::DIRECTION_OUT;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing IncomingMail model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(self::URL_ROOT_AS_ARRAY);
            }
        }
        else {
            $model->counteragent = $model->ca_id;
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing IncomingMail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::URL_ROOT_AS_ARRAY);
    }

    /**
     * Finds the IncomingMail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IncomingMail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IncomingMail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Рендерит поле с исходящим номером.
     * @param int $org_id идентификатор организации
     * @return mixed
     */
    public function actionRenderFieldOutcNum($org_id)
    {
        $model = new IncomingMail(['org_id' => $org_id]);
        $model->calcNextNumber('om_num_tmpl');

        return $this->renderAjax('_field_inc_num', [
            'model' => $model,
            'form' => \yii\bootstrap\ActiveForm::begin(),
        ]);
    }
}
