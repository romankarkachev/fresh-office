<?php

namespace backend\controllers;

use Yii;
use common\models\EcoTypes;
use common\models\EcoTypesSearch;
use common\models\EcoTypesMilestones;
use common\models\EcoTypesMilestonesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * EcoTypesController implements the CRUD actions for EcoTypes model.
 */
class EcoTypesController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'eco-types';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Типы проектов';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Типы проектов по экологии';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_MILESTONES_SORT_PAGING = 'eco-types/update';

    /**
     * URL для обработки формы добавления нового этапа
     */
    const URL_ADD_MILESTONE = 'add-types-milestone';

    /**
     * URL для обработки формы добавления нового этапа в виде массива
     */
    const URL_ADD_MILESTONE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_ADD_MILESTONE];

    /**
     * URL для удаления этапа проекта
     */
    const URL_DELETE_MILESTONE = 'delete-types-milestone';

    /**
     * URL для удаления этапа проекта в виде массива
     */
    const URL_DELETE_MILESTONE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_MILESTONE];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index', 'create', 'update', 'delete',
                            'types-milestones-list', self::URL_ADD_MILESTONE, self::URL_DELETE_MILESTONE,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'ecologist_head'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    self::URL_DELETE_MILESTONE => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку этапов для типа проектов, переданного в параметрах.
     * @param $type_id integer of EcoTypes
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchTypeMilestones($type_id)
    {
        $searchModel = new EcoTypesMilestonesSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'type_id' => $type_id,
            ]
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель этапов проектов с заданным типом.
     * @param $type_id integer тип проектов, для которого создается этап
     * @return EcoTypesMilestones
     */
    private function createNewMilestoneModel($type_id)
    {
        $next_num = null;

        try {
            $next_num = (EcoTypesMilestones::find()->select('MAX(order_no)')->where(['type_id' => $type_id])->scalar()) + 1;
        }
        catch (\Exception $e) {}

        return new EcoTypesMilestones([
            'type_id' => $type_id,
            'is_affects_to_cycle_time' => true,
            'order_no' => $next_num,
        ]);
    }

    /**
     * Lists all EcoTypes models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EcoTypesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new EcoTypes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new EcoTypes();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing EcoTypes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(self::ROOT_URL_AS_ARRAY);
        }

        return $this->render('update', [
            'model' => $model,
            'dpMilestones' => $this->fetchTypeMilestones($id),
            'newMilestoneModel' => $this->createNewMilestoneModel($id),
        ]);
    }

    /**
     * Deletes an existing EcoTypes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->checkIfUsed())
            return $this->render('/common/cannot_delete', [
                'details' => [
                    'breadcrumbs' => ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY],
                    'modelRep' => $model->name,
                    'buttonCaption' => self::ROOT_LABEL,
                    'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                ],
            ]);

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the EcoTypes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return EcoTypes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EcoTypes::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Отображает список этапов, идентификатор которого передается в параметрах.
     * @param $id integer идентификатор запроса
     * @return mixed
     */
    public function actionTypesMilestonesList($id)
    {
        return $this->render('milestones_list', [
            'dataProvider' => $this->fetchTypeMilestones($id),
            'model' => $this->createNewMilestoneModel($id),
            'action' => self::URL_ADD_MILESTONE,
        ]);
    }

    /**
     * Выполняет интерактивное добавление этапа к типу проектов.
     * @return mixed
     */
    public function actionAddTypesMilestone()
    {
        if (Yii::$app->request->isPjax) {
            $model = new EcoTypesMilestones();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->render('milestones_list', [
                    'dataProvider' => $this->fetchTypeMilestones($model->type_id),
                    'model' => $this->createNewMilestoneModel($model->type_id),
                    'action' => self::URL_ADD_MILESTONE,
                ]);
            }
        }

        return false;
    }

    /**
     * Выполняет удаление этапа из типа проектов.
     * @param $id integer идентификатор этапа проектов, который надо удалить
     * @return mixed
     */
    public function actionDeleteTypesMilestone($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = EcoTypesMilestones::findOne($id);
            if ($model) {
                $type_id = $model->type_id;
                $model->delete();

                return $this->render('milestones_list', [
                    'dataProvider' => $this->fetchTypeMilestones($type_id),
                    'model' => $this->createNewMilestoneModel($type_id),
                    'action' => self::URL_ADD_MILESTONE,
                ]);
            }
        }

        return false;
    }
}
