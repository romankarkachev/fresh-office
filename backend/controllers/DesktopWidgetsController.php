<?php

namespace backend\controllers;

use Yii;
use common\models\DesktopWidgets;
use common\models\DesktopWidgetsSearch;
use common\models\DesktopWidgetsAccess;
use common\models\DesktopWidgetsAccessSearch;
use common\models\AuthItem;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * DesktopWidgetsController implements the CRUD actions for DesktopWidgets model.
 */
class DesktopWidgetsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = 'desktop-widgets';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Виджеты для Рабочего стола';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY];

    /**
     * URL для редактирования виджета
     */
    const URL_UPDATE = 'update';
    const URL_UPDATE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_UPDATE];

    /**
     * URL для интерактивного добавления новой записи об размещении на Рабочем столе
     */
    const URL_ADD_USAGE = 'add-usage';
    const URL_ADD_USAGE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_ADD_USAGE];

    /**
     * URL для интерактивного удаления
     */
    const URL_DELETE_USAGE = 'delete-usage';
    const URL_DELETE_USAGE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_DELETE_USAGE];

    /**
     * URL для рендера поля с идентификатором сущности
     */
    const URL_RENDER_ENTITY_FIELD = 'render-entity-field';
    const URL_RENDER_ENTITY_FIELD_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_RENDER_ENTITY_FIELD];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
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
     * Lists all DesktopWidgets models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DesktopWidgetsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new DesktopWidgets model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DesktopWidgets();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(ArrayHelper::merge(self::URL_UPDATE_AS_ARRAY, ['id' => $model->id]));
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing DesktopWidgets model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    return $this->redirect(self::URL_ROOT_AS_ARRAY);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'newUsageModel' => $this->createNewUsageModel($id),
            'dpUsage' => $this->fetchUsage($id),
        ]);
    }

    /**
     * Deletes an existing DesktopWidgets model.
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
     * Finds the DesktopWidgets model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DesktopWidgets the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DesktopWidgets::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::$app->params['promptPageNotFound']);
    }

    /**
     * Делает выборку записей об использовании виджета у себя на Рабочем столе.
     * @param $widget_id integer
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchUsage($widget_id)
    {
        $searchModel = new DesktopWidgetsAccessSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['widget_id' => $widget_id]]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель записи об использовании виджета у себя на Рабочем столе.
     * @param integer $widget_id виджет, к которому открывается доступ
     * @return DesktopWidgetsAccess
     */
    private function createNewUsageModel($widget_id = null)
    {
        return new DesktopWidgetsAccess([
            'widget_id' => $widget_id,
        ]);
    }

    /**
     * Рендерит список сущностей, применяющих виджет.
     * @param integer $widget_id идентификатор виджета
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderUsage($widget_id)
    {
        return $this->renderAjax('_usage_list', [
            'dataProvider' => $this->fetchUsage($widget_id),
            'model' => $this->createNewUsageModel($widget_id),
        ]);
    }

    /**
     * Рендерит поле для выбора идентификатора сущности.
     * @param $type integer тип сущности
     * @return string
     */
    public function actionRenderEntityField($type)
    {
        $label = 'Выберите';
        $data = [];
        switch ($type) {
            case DesktopWidgetsAccess::TYPE_ROLE:
                $data = AuthItem::arrayMapForSelect2();
                $label = 'Роль';
                break;
            case DesktopWidgetsAccess::TYPE_USER:
                $data = User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES);
                $label = 'Пользователь';
                break;
        }

        return $this->renderAjax('_field_entity', [
            'model' => $this->createNewUsageModel(),
            'form' => new \yii\bootstrap\ActiveForm(),
            'data' => $data,
            'label' => $label,
        ]);
    }

    /**
     * Выполняет интерактивное добавление записи о размещении виджета у себя на Рабочем столе.
     * add-usage
     * @return mixed
     * @throws \Throwable
     */
    public function actionAddUsage()
    {
        if (Yii::$app->request->isPjax) {
            $model = new DesktopWidgetsAccess();

            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if ($model->save()) {
                        $transaction->commit();
                        return $this->renderUsage($model->widget_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }

        return false;
    }

    /**
     * Выполняет интерактивное удаление записи об использовании виджета у себя на Рабочем столе.
     * delete-usage
     * @param integer $id идентификатор записи
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteUsage($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = DesktopWidgetsAccess::findOne($id);
            if ($model) {
                $widget_id = $model->widget_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderUsage($widget_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }

        return false;
    }
}
