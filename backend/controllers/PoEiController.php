<?php

namespace backend\controllers;

use Yii;
use common\models\PoEi;
use common\models\PoEiSearch;
use common\models\Po;
use common\models\PoEiReplaceForm;
use common\models\foProjects;
use common\models\PoAt;
use common\models\PoPop;
use common\models\UsersEiAccess;
use common\models\UsersEiApproved;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * PoEiController implements the CRUD actions for PoEi model.
 */
class PoEiController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'po-ei';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Статьи расходов';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * Замена одной статьи расходов платежных ордеров на другую
     */
    const URL_MASS_REPLACE = 'mass-replace';
    const URL_MASS_REPLACE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_MASS_REPLACE];

    /**
     * URL для подсчета подходящих под массовую замену платежных ордеров
     */
    const URL_COUNT_POS = 'count-pos';
    const URL_COUNT_POS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_COUNT_POS];

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
                        'actions' => ['index', 'create', 'update', self::URL_MASS_REPLACE, self::URL_COUNT_POS],
                        'allow' => true,
                        'roles' => ['@'],
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
     * Lists all PoEi models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PoEiSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => Yii::$app->request->get($searchModel->formName()) != null,
        ]);
    }

    /**
     * Creates a new PoEi model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PoEi();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PoEi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PoEi model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->checkIfUsed()) {
            // ссылки на статью расходов есть в платежных ордерах по бюджету или свойствах статей расходов
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
        }

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the PoEi model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PoEi the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PoEi::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Отображает форму обработки статей расходов в платежных ордерах.
     * Необходимо выбрать две статьи расходов: одну искомую и вторую для замены. Выполняется в транзакции.
     * mass-replace
     * @return mixed
     * @throws \Exception если произойдет ошибка в выполнении запроса
     * @throws \Throwable
     */
    public function actionMassReplace()
    {
        $model = new PoEiReplaceForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->src_ei_id != $model->dest_ei_id) {
                    // для начала убедимся, что обе они существуют
                    $modelNew = PoEi::findOne($model->dest_ei_id);
                    $modelOld = PoEi::findOne($model->src_ei_id);

                    if (!empty($modelNew) && !empty($modelOld)) {
                        $deletedAppend = '';
                        $model->src_ei_name = $modelOld->name;

                        $connection = Yii::$app->db;
                        $transaction = $connection->beginTransaction();
                        try {
                            // выполним массовую замену
                            $rowsAffected = $connection->createCommand()->update(Po::tableName(), [
                                // новое значение
                                'ei_id' => $model->dest_ei_id,
                            ], [
                                // условие
                                'ei_id' => $model->src_ei_id,
                            ])->execute();

                            if ($rowsAffected > 0) {
                                // удалим старую статью расходов
                                if ($model->drop_released) {
                                    // удаляем из таблицы статей, требующих согласования
                                    UsersEiApproved::deleteAll(['ei_id' => $modelOld->id]);
                                    // удаляем из таблицы статей, к которым имеют доступ отдельные сотрудники
                                    UsersEiAccess::deleteAll(['ei_id' => $modelOld->id]);
                                    // удаляем из шаблонов автоплатежей
                                    PoAt::deleteAll(['ei_id' => $modelOld->id]);
                                    // еще статья может быть использована в свойствах платежных ордеров
                                    PoPop::deleteAll(['ei_id' => $modelOld->id]);

                                    if ($modelOld->delete()) {
                                        $deletedAppend = ' Статья расходов &laquo;' . $model->src_ei_name . '&raquo; (ID ' . $model->src_ei_id . ') успешно удалена.';
                                    }
                                    else {
                                        $deletedAppend = ' Статья была отмечена к удалению, но сделать это не удалось.';
                                    }
                                }

                                $transaction->commit();

                                Yii::$app->session->setFlash('success', 'Замена успешно выполнена. Количество платежных ордеров, которые затронули изменения: ' . $rowsAffected . '.' . $deletedAppend);
                                return $this->redirect(Url::to(self::ROOT_URL_AS_ARRAY));
                            }
                            else {
                                Yii::$app->session->setFlash('warning', 'Изменения не затронули ни одну строку. Вероятно, старая статья расходов нигде не использована. В этом случае удалите ее самостоятельно из справочника &laquo;' . \yii\helpers\Html::a(PoEiController::ROOT_LABEL, [
                                    '/po-ei', 'PoEiSearch' => ['id' => $model->src_ei_id],
                                ], ['target' => '_blank', 'title' => 'Откроется в новом окне']). '&raquo;.');
                            }
                        }
                        catch (\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                    }
                    else Yii::$app->session->setFlash('warning', 'Одна или обе из выбранных статей расходов не существует. Ничего не было выполнено.');
                }
                else Yii::$app->session->setFlash('warning', 'Выбрана одна и та же статья расходов. Ничего не было выполнено.');
            }
        }

        return $this->render('mass_replace', [
            'model' => $model,
        ]);
    }

    /**
     * Подсчитывает количество использований статьи расходов, переданной в параметрах, и возвращает форматированный результат.
     * count-pos
     * @param $id integer
     * @return string|bool
     */
    public function actionCountPos($id)
    {
        $id = intval($id);
        if ($id > 0) {
            $count = Po::find()->where(['ei_id' => $id])->count();
            if ($count > 0)
                return 'Статья расходов использована в ' . foProjects::declension($count, ['платежном ордере', 'платежных ордерах', 'платежных ордерах']) . '.';
            else
                return 'Данный вид упаковки не использован ни в одной строке табличной части.';
        }

        return false;
    }
}
