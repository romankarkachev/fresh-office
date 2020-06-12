<?php

namespace backend\controllers;

use common\models\FinanceObligationDelegationForm;
use Yii;
use common\models\FinanceAdvanceHolders;
use common\models\FinanceAdvanceHoldersSearch;
use common\models\FinanceTransactions;
use common\models\FinanceTransactionsSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * AdvanceHoldersController implements the CRUD actions for FinanceAdvanceHolders model.
 */
class AdvanceHoldersController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'advance-holders';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Подотчет';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Взаиморасчеты с подотчетными лицами';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для создания документа по выдаче денежных средств подотчет
     */
    const URL_CREATE = 'create';
    const URL_CREATE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE];

    /**
     * URL для создания документа по выдаче денежных средств подотчет
     */
    const URL_TRANSACTIONS = 'transactions';
    const URL_TRANSACTIONS_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_TRANSACTIONS];

    /**
     * URL для делегирования долга другому подотчетному лицу
     */
    const URL_DELEGATION = 'delegation';
    const URL_DELEGATION_LABEL = 'Передача долга';
    const URL_DELEGATION_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELEGATION];

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
                        'actions' => ['index', self::URL_DELEGATION],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [self::URL_CREATE, self::URL_TRANSACTIONS],
                        'allow' => true,
                        'roles' => ['root', 'accountant', 'accountant_b'],
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
     * Делает выборку финансовых транзакций по пользователю, переданному в параметрах.
     * @param $searchModel FinanceTransactionsSearch
     * @param int $id идентификатор пользователя, чьи транзакции выводятся
     * @param bool $renderRestricted признак, определяющий возможности при работе в этом разделе
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function fetchFinanceTransactions($searchModel, $id, $renderRestricted = false)
    {
        $route = null;
        if ($renderRestricted) {
            $route = self::ROOT_URL_FOR_SORT_PAGING;
        }

        return $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, [
            $searchModel->formName() => ['user_id' => $id],
        ]), $route);
    }

    /**
     * Рендерит список финансовых транзакций.
     * @param int $id идентификатор пользователя, чьи транзакции выводятся
     * @param bool $renderRestricted признак, определяющий возможности при работе в этом разделе
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function renderFinanceTransactions($id, $renderRestricted = false)
    {
        $searchModel = new FinanceTransactionsSearch();

        return $this->render('transactions', [
            'searchModel' => $searchModel,
            'dataProvider' => $this->fetchFinanceTransactions($searchModel, $id, $renderRestricted),
            'renderRestricted' => $renderRestricted,
        ]);
    }

    /**
     * Lists all FinanceAdvanceHolders models.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b')) {
            // для подотчетных лиц рендерится таблица взаимаорасчетов именно с ними
            return $this->renderFinanceTransactions(Yii::$app->user->id, true);
        }
        else {
            // для пользователей с полными правами или бухгалтеров по бюджету доступны все инструменты
            $searchModel = new FinanceAdvanceHoldersSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Отображает финансовые транзакции в части взаиморасчетов с подотчетным лицом.
     * @param integer $id
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTransactions($id)
    {
        return $this->renderFinanceTransactions($id);
    }

    /**
     * Creates a new FinanceAdvanceHolders model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FinanceTransactions();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(self::ROOT_URL_AS_ARRAY);
            }
        }
        else {
            $model->operation = FinanceTransactions::OPERATION_ВЫДАЧА_ПОДОТЧЕТ;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing FinanceAdvanceHolders model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        //$this->findModel($id)->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the FinanceAdvanceHolders model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FinanceAdvanceHolders the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FinanceAdvanceHolders::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::$app->params['promptPageNotFound']);
    }

    /**
     * Делегирование финансовых обязательств (передача денежных средств от одного пользователя другому).
     * @return string|\yii\web\Response
     * @throws \Throwable
     */
    public function actionDelegation()
    {
        $model = new FinanceObligationDelegationForm();
        $balance = Yii::$app->user->identity->advanceBalanceStored;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (!empty($model->amount) && ($balance < $model->amount)) {
                    $model->addError('amount', 'Введенная сумма превышает размер задолженности.');
                }
                else {
                    $result = $model->delegateFinanceObligations();
                    if ($result['result']) {
                        Yii::$app->session->setFlash('success', $result['message']);
                        return $this->redirect(self::ROOT_URL_AS_ARRAY);
                    }
                    else {
                        Yii::$app->session->setFlash('error', 'Ошибка при передаче финансовых обязательств!<br />' . (!empty($result['message']) ? $result['message'] : ''));
                    }
                }
            }
        }
        else {
            if (false !== $balance) {
                if ($balance > 0) {
                    // баланс положительный, это означает, что задолженность присутствует
                    $model->amount = $balance;
                }
            }
        }

        return $this->render('delegation', [
            'model' => $model,
        ]);
    }
}
