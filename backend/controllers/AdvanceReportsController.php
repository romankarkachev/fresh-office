<?php

namespace backend\controllers;

use Yii;
use common\models\AdvanceReportForm;
use common\models\Po;
use common\models\PoSearch;
use common\models\PaymentOrdersStates;
use common\models\PaymentOrdersSearch;
use common\models\UsersEiApproved;
use yii\base\Model;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * AdvanceHoldersController implements the CRUD actions for FinanceAdvanceHolders model.
 */
class AdvanceReportsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const URL_ROOT_FOR_SORT_PAGING = 'advance-reports';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Авансовые отчеты';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::URL_ROOT_AS_ARRAY];

    /**
     * URL для создания авансового отчета подотчетником
     */
    const URL_NEW = 'new';
    const URL_NEW_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_NEW];

    /**
     * URL для просмотра авансового отчета подотчетником
     */
    const URL_VIEW = 'view';
    const URL_VIEW_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_VIEW];

    /**
     * URL для модерации авансового отчета подотчетника бухгалтером
     */
    const URL_MODERATE = 'moderate';
    const URL_MODERATE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_MODERATE];

    /**
     * URL для удаления авансового отчета подотчетника
     */
    const URL_DELETE = 'delete';
    const URL_DELETE_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_DELETE];

    /**
     * URL для рендера новой строки (будущего авансового отчета)
     */
    const URL_RENDER_PO_ROW = 'render-po-row';
    const URL_RENDER_PO_ROW_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_RENDER_PO_ROW];

    /**
     * URL для рендера новой строки (будущего авансового отчета)
     */
    const URL_SET_PAID_ON_THE_FLY = 'set-paid-on-the-fly';
    const URL_SET_PAID_ON_THE_FLY_AS_ARRAY = ['/' . self::URL_ROOT_FOR_SORT_PAGING . '/' . self::URL_SET_PAID_ON_THE_FLY];

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
                        'actions' => [self::URL_MODERATE],
                        'allow' => true,
                        'roles' => ['root', 'accountant', 'accountant_b'],
                    ],
                    [
                        'actions' => ['index', self::URL_NEW, self::URL_VIEW, self::URL_RENDER_PO_ROW, self::URL_SET_PAID_ON_THE_FLY, self::URL_DELETE],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    /*
                    [
                        'actions' => [self::URL_DELETE],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                    */
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
     * {@inheritDoc}
     */
    public function beforeAction($action) {
        if (!Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b')) {
            if (false === Yii::$app->user->identity->advanceBalanceCalculated) {
                // если пользователь никогда не получал авансы, то в этом разделе ему делать нечего
                // не касается полных прав и бухгалтеров по бюджету
                throw new ForbiddenHttpException('Доступ запрещен.');
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Отображает список авансовых отчетов.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new PoSearch();
        $filterRequired = [
            $searchModel->formName() => ['searchGroupStates' => PaymentOrdersSearch::CLAUSE_STATES_GROUP_ADVANCE_REPORTS],
        ];

        $dataProvider = $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, $filterRequired), AdvanceReportsController::URL_ROOT_FOR_SORT_PAGING);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'renderRestricted' => !Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b'),
        ]);
    }

    /**
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionNew()
    {
        $model = new AdvanceReportForm();
        $modelsPos = [];
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            // загрузим модели платежных ордеров
            if (isset(Yii::$app->request->post('AdvanceReportForm')['crudePos'])) {
                foreach (Yii::$app->request->post('AdvanceReportForm')['crudePos'] as $i => $data) {
                    $newModel = new Po();
                    $newModel->load($data, '');
                    $newModel->state_id = PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ;
                    $newModel->files = UploadedFile::getInstancesByName('AdvanceReportForm[crudePos][' . $i . '][files]');
                    $modelsPos[$i] = $newModel;
                }
                if (isset($newModel)) unset($newModel);

                $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

                try {
                    $valid = $model->validate();
                    $valid = Model::validateMultiple($modelsPos) && $valid;
                    if ($valid) {
                        $success = true;
                        // создаем платежные ордера
                        foreach ($modelsPos as $newModel) {
                            if ($newModel->save(false)) {
                                if (!empty($newModel->files)) {
                                    $success = $newModel->upload();
                                }
                            }
                        }

                        if ($success) {
                            $transaction->commit();
                            return $this->redirect(AdvanceReportsController::URL_ROOT_AS_ARRAY);
                        }
                        else {
                            $transaction->rollBack();
                        }
                    } else {
                        $transaction->rollBack();
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw new BadRequestHttpException($e->getMessage(), 0, $e);
                }

                $model->crudePos = $modelsPos;
            }
        }

        return $this->render('new', [
            'model' => $model,
        ]);
    }

    /**
     * Открывает авансовый отчет на просмотр. Используется подотчетниками для просмотра своих документов.
     * @param int $id идентификатор платежного ордера в статусе "Авансовый отчет"
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (!in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ) || (!Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b') && $model->created_by != Yii::$app->user->id)) {
            // из этого раздела доступ возможен только в авансовые отчеты (другие платежные ордеры открыть нельзя)
            // если открывает не пользователь с полными правами и не бухгалтер по бюджету, то платежный ордер должен
            // принадлежать открывающему
            throw new ForbiddenHttpException('Доступ запрещен.');
        }

        return $this->render('/po/view', [
            'model' => $model,
            'dpFiles' => $model->getFilesAsDataProvider(), // прикрепленные к авансовому отчету файлы
            'dpLogs' => $model->getLogsAsDataProvider(),
            'dpProperties' => $model->getPropertiesAsDataProvider(), // свойства статьи расходов платежного ордера
        ]);
    }

    /**
     * Updates an existing Po model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException если платежный ордер не будет обнаружен
     * @throws \yii\base\InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionModerate($id)
    {
        $model = $this->findModel($id);

        if (!in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ) || (!Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b') && $model->created_by != Yii::$app->user->id)) {
            // из этого раздела доступ возможен только в авансовые отчеты (другие платежные ордеры открыть нельзя)
            // если открывает не пользователь с полными правами и не бухгалтер по бюджету, то платежный ордер должен
            // принадлежать открывающему
            throw new ForbiddenHttpException('Доступ запрещен.');
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                // если нажата кнопка "Принять авансовый отчет"
                if (Yii::$app->request->post('ar_approve') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН;

                // если нажата кнопка "Отклонить авансовый отчет"
                if (Yii::$app->request->post('ar_reject') !== null) $model->state_id = PaymentOrdersStates::PAYMENT_STATE_ОТКЛОНЕННЫЙ_АВАНСОВЫЙ_ОТЧЕТ;

                if ($model->save()) {
                    return $this->redirect(self::URL_ROOT_AS_ARRAY);
                }
                else {
                    // возвращаем статус
                    $model->state_id = PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ;
                }
            }
        }

        return $this->render('/po/update', [
            'model' => $model,
            'dpFiles' => $model->getFilesAsDataProvider(), // прикрепленные к авансовому отчету файлы
            'dpLogs' => $model->getLogsAsDataProvider(),
            'dpProperties' => $model->getPropertiesAsFilledArray(), // свойства статьи расходов платежного ордера
        ]);
    }

    /**
     * Удаляет платежный ордер в статусе "Авансовый отчет".
     * Доступно только для пользователей с полными правами, выполняется перенаправление в список авансовых отчетов.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если платежный ордер не будет обнаружен
     * @throws ForbiddenHttpException при попытке удалить не авансовый отчет
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ) && (Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b') || $model->created_by == Yii::$app->user->id)) {
            // если удаление производит пользователь с полными правами, бухгалтер по бюджету, автор и платежный ордер
            // является авансовым отчетом, тогда операция допустима
            $model->delete();
        }
        else {
            throw new ForbiddenHttpException('Недопустимая попытка удалить объект.');
        }

        return $this->redirect(self::URL_ROOT_AS_ARRAY);
    }

    /**
     * Рендерит строку "Авансового отчета".
     * render-po-row
     * @param integer $counter номер строки по порядку
     * @return mixed
     */
    public function actionRenderPoRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_row_po_fields', [
                'model' => new Po(),
                'formModel' => new AdvanceReportForm(),
                'form' => new \yii\bootstrap\ActiveForm(),
                'counter' => (intval($counter) + 1),
            ]);
        }

        return false;
    }

    /**
     * Выполняет интерактивное изменение статуса платежного ордера в статусе "Авансовый отчет" на "Оплачено".
     * set-paid-on-the-fly
     * @param $po_id integer идентфикатор платежного ордера
     * @param $state_id integer новый статус, который необходимо присвоить
     * @return mixed
     */
    public function actionSetPaidOnTheFly($po_id, $state_id)
    {
        $state_id = intval($state_id);
        $state = PaymentOrdersStates::findOne($state_id);

        $po_id = intval($po_id);
        $model = Po::findOne($po_id);
        if ($state != null && $model != null) {
            // бухгалтер по бюджету должен обладать правом согласования платежей, проверяется следующим образом:
            if (
                Yii::$app->user->can('accountant_b') &&
                (!in_array($model->ei_id, UsersEiApproved::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])->column()) ||
                    $model->amount > (float)Yii::$app->user->identity->profile->po_maa)
            ) return false;

            $model->state_id = $state_id;
            return $model->save(false);
        }

        return false;
    }

    /**
     * Finds the Po model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Po the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Po::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::$app->params['promptPageNotFound']);
    }
}
