<?php

namespace backend\controllers;

use Yii;
use common\models\PoProperties;
use common\models\PoPropertiesSearch;
use common\models\PoEip;
use common\models\PoEipSearch;
use common\models\PoValues;
use common\models\PoValuesSearch;
use yii\base\Model;
use yii\db\Transaction;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PoPropertiesController implements the CRUD actions for PoProperties model.
 */
class PoPropertiesController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'po-properties';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Свойства статей расходов';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    /**
     * URL для рендера строки с новым значением свойства
     */
    const URL_RENDER_VALUE_ROW = 'render-value-row';
    const URL_RENDER_VALUE_ROW_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_VALUE_ROW];

    /**
     * URL для интерактивного добавления нового значения свойства
     */
    const URL_CREATE_VALUE = 'create-value';
    const URL_CREATE_VALUE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE_VALUE];

    /**
     * URL для интерактивного удаления значения свойства
     */
    const URL_DELETE_VALUE = 'delete-value';
    const URL_DELETE_VALUE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_VALUE];

    /**
     * URL для интерактивной привязки значения свойства к статье
     */
    const URL_LINK_ITEM = 'link-item';
    const URL_LINK_ITEM_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_LINK_ITEM];

    /**
     * URL для интерактивного удаления привязки значения свойства к статье
     */
    const URL_DROP_LINK = 'drop-link';
    const URL_DROP_LINK_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DROP_LINK];

    /**
     * URL для интерактивного переименования значения свойства
     */
    const URL_RENAME_VALUE = 'rename-value';
    const URL_RENAME_VALUE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENAME_VALUE];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index', 'create', self::URL_UPDATE, 'delete', self::URL_RENAME_VALUE,
                            self::URL_RENDER_VALUE_ROW, self::URL_CREATE_VALUE, self::URL_DELETE_VALUE,
                            self::URL_LINK_ITEM, self::URL_DROP_LINK,
                        ],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    self::URL_RENAME_VALUE => ['POST'],
                    self::URL_DELETE_VALUE => ['POST'],
                    self::URL_DROP_LINK => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку значений свойства, переданного в параметрах.
     * @param integer $property_id идентификатор свойства
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchValues($property_id)
    {
        $searchModel = new PoValuesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['property_id' => $property_id]]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель значения свойства.
     * @param integer $property_id свойство, к которому создается новое значение
     * @return PoValues
     */
    private function createNewValueModel($property_id)
    {
        return new PoValues(['property_id' => $property_id]);
    }

    /**
     * Рендерит список значений свойства.
     * @param integer $property_id идентификатор свойства
     * @return mixed
     */
    private function renderValues($property_id)
    {
        return $this->renderAjax('/po-values/_values_list', [
            'dataProvider' => $this->fetchValues($property_id),
            'model' => $this->createNewValueModel($property_id),
        ]);
    }

    /**
     * Делает выборку статей, описанных свойством, переданным в параметрах.
     * @param integer $property_id идентификатор свойства
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchItems($property_id)
    {
        $searchModel = new PoEipSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['property_id' => $property_id]]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель связки свойства и статьи расходов.
     * @param integer $property_id свойство, к которому привязывается статья
     * @return PoEip
     */
    private function createNewEPModel($property_id)
    {
        return new PoEip(['property_id' => $property_id]);
    }

    /**
     * Рендерит список статей расходов, описанных данным свойство.
     * @param integer $property_id идентификатор свойства
     * @return mixed
     */
    private function renderItems($property_id)
    {
        return $this->renderAjax('/po-ei/_items_list', [
            'dataProvider' => $this->fetchItems($property_id),
            'model' => $this->createNewEPModel($property_id),
        ]);
    }

    /**
     * Lists all PoProperties models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PoPropertiesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new PoProperties model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new PoProperties();
        $modelsValues = [];

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            // загрузим модели значений свойств
            if (isset(Yii::$app->request->post($model->formName())['values'])) {
                foreach (Yii::$app->request->post($model->formName())['values'] as $i => $data) {
                    $newModel = new PoValues();
                    $newModel->load($data, '');
                    $modelsValues[$i] = $newModel;
                }
            }

            // выполнять операцию будем в транзакции, поскольку необходимо иметь возможность отменить все изменения,
            // если хотя бы хоть что-нибудь пойдет не так
            $success = true;
            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

            try {
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelsValues) && $valid;

                if ($valid) {
                    // основная модель успешно прошла валидацию, запишем ее
                    if ($model->save()) {
                        // записываем в базу значения свойств
                        foreach ($modelsValues as $newModel) {
                            $newModel->property_id = $model->id;
                            if (!$newModel->save(false)) {
                                $success = false;
                                break;
                            }
                        }

                        // сохраняем описание статей данным свойством
                        if ($success && (is_array($model->ei) && count($model->ei) > 0)) {
                            foreach ($model->ei as $item) {
                                if (!(new PoEip([
                                    'ei_id' => $item,
                                    'property_id' => $model->id,
                                ]))->save()) {
                                    $success = false;
                                    break;
                                }
                            }
                        }
                    } else {
                        $success = false;
                    }

                    if ($success) {
                        $transaction->commit();
                        return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_UPDATE, 'id' => $model->id]);
                    }
                    else {
                        $transaction->rollBack();
                    }
                }
            }
            catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            catch(\Throwable $e) {
                $transaction->rollBack();
            }

            $model->values = $modelsValues;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PoProperties model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(self::ROOT_URL_AS_ARRAY);
            }
        }
        else {
            $model->values = $this->fetchValues($id);
            $model->ei = $this->fetchItems($id);
        }

        return $this->render('update', [
            'model' => $model,
            'newValueModel' => $this->createNewValueModel($model->id),
            'newEPModel' => $this->createNewEPModel($model->id),
        ]);
    }

    /**
     * Deletes an existing PoProperties model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
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
                    'action1' => 'удалить',
                    'action2' => 'удален',
                ],
            ]);

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the PoProperties model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PoProperties the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PoProperties::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Рендерит строку со значением свойства.
     * @param $counter integer
     * @return string
     */
    public function actionRenderValueRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_row_value', [
                'property' => new PoProperties(),
                'model' => new PoValues(),
                'counter' => $counter + 1,
                'form' => new \yii\bootstrap\ActiveForm(),
            ]);
        }
    }

    /**
     * Выполняет интерактивное создание значения свойства.
     * create-value
     * @return bool|mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionCreateValue()
    {
        if (Yii::$app->request->isPjax) {
            $model = new PoValues();

            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        $transaction->commit();
                        return $this->renderValues($model->property_id);
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
     * Выполняет удаление значения свойства.
     * delete-value
     * @param $id integer идентификатор свойства, которое надо удалить
     * @return mixed
     * @throws \Throwable
     */
    public function actionDeleteValue($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = PoValues::findOne($id);
            if ($model) {
                $property_id = $model->property_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderValues($property_id);
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
    }

    /**
     * Выполняет интерактивное добавление связки значения свойства и статьи расходов.
     * link-item
     * @return bool|mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionLinkItem()
    {
        if (Yii::$app->request->isPjax) {
            $model = new PoEip();

            if ($model->load(Yii::$app->request->post())) {
                // добавление в базу будем осуществлять, только если такая статья отсутствует среди связанных
                $exist = PoEip::findOne([
                    'ei_id' => $model->ei_id,
                    'property_id' => $model->property_id,
                ]);
                if ($exist) return $this->renderItems($model->property_id);

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        $transaction->commit();
                        return $this->renderItems($model->property_id);
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
     * Выполняет удаление привязки значения свойства к статье расходов.
     * drop-link
     * @param $id integer идентификатор связки, которую надо удалить
     * @return mixed
     * @throws \Throwable
     */
    public function actionDropLink($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = PoEip::findOne($id);
            if ($model) {
                $property_id = $model->property_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderItems($property_id);
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
    }

    /**
     * Выполняет интерактивное переименование значение свойства.
     * @return bool
     */
    public function actionRenameValue()
    {
        if (Yii::$app->request->isPost) {
            $id = Yii::$app->request->post('id');
            $name = Yii::$app->request->post('name');
            if (null !== $id && null !== $name) {
                $model = PoValues::findOne($id);
                if ($model) {
                    // значение обнаружено, выполняем переименование, сохраняем и отдаем ответ
                    $model->name = $name;
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return $model->save();
                }
            }
        }

        return false;
    }
}
