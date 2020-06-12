<?php

namespace backend\controllers;

use moonland\phpexcel\Excel;
use Yii;
use common\models\Organizations;
use common\models\OrganizationsSearch;
use common\models\OrganizationsBas;
use common\models\OrganizationsBasSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * OrganizationsController implements the CRUD actions for Organizations model.
 */
class OrganizationsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'organizations';

    /**
     * URL, применяемый для сортировки и постраничного перехода по списку банковских счетов
     */
    const BANK_ACCOUNTS_URL_FOR_SORT_PAGING = self::ROOT_URL_FOR_SORT_PAGING;

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = self::ROOT_LABEL;

    /**
     * Название списка записей
     */
    const ROOT_LABEL = 'Организации';

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_BANK_ACCOUNTS_SORT_PAGING = self::ROOT_URL_FOR_SORT_PAGING . '/update';

    /**
     * URL для обработки формы добавления нового банковского счета
     */
    const URL_ADD_BANK_ACCOUNT = 'add-bank-account';

    /**
     * URL для обработки формы добавления нового банковского счета в виде массива
     */
    const URL_ADD_BANK_ACCOUNT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_ADD_BANK_ACCOUNT];

    /**
     * URL для удаления банковского счета
     */
    const URL_DELETE_BANK_ACCOUNT = 'delete-bank-account';

    /**
     * URL для удаления банковского счета в виде массива
     */
    const URL_DELETE_BANK_ACCOUNT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_BANK_ACCOUNT];

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
                        'actions' => [
                            'index', 'create', 'update', 'delete',
                            'bank-accounts-list', self::URL_ADD_BANK_ACCOUNT, self::URL_DELETE_BANK_ACCOUNT,
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
                ],
            ],
        ];
    }

    /**
     * Делает выборку банковских счетов организации, переданной в параметрах.
     * @param $org_id integer of OrganizationsBas
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchBankAccounts($org_id)
    {
        $searchModel = new OrganizationsBasSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'org_id' => $org_id,
            ]
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель банковского счета организации.
     * @param $org_id integer организация, для которой создается банковский счет
     * @return OrganizationsBas
     */
    private function createNewBankAccountModel($org_id)
    {
        return new OrganizationsBas([
            'org_id' => $org_id,
        ]);
    }

    /**
     * Lists all Organizations models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrganizationsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Organizations model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Organizations();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Organizations model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::ROOT_URL_AS_ARRAY);
        } else {
            return $this->render('update', [
                'model' => $model,
                'dpBankAccounts' => $this->fetchBankAccounts($id),
                'newBankAccountModel' => $this->createNewBankAccountModel($id),
            ]);
        }
    }

    /**
     * Deletes an existing Organizations model.
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
                    'action1' => 'удалить',
                    'action2' => 'удален',
                ],
            ]);

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the Organizations model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Organizations the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Organizations::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Отображает список диалогов запроса, идентификатор которого передается в параметрах.
     * @param $id integer идентификатор запроса
     * @return mixed
     */
    public function actionBankAccountsList($id)
    {
        return $this->render('ba_list', [
            'dataProvider' => $this->fetchBankAccounts($id),
            'model' => $this->createNewBankAccountModel($id),
            'action' => self::URL_ADD_BANK_ACCOUNT,
        ]);
    }

    /**
     * Выполняет интерактивное добавление банковского счета к организации.
     * @return mixed
     */
    public function actionAddBankAccount()
    {
        if (Yii::$app->request->isPjax) {
            $model = new OrganizationsBas();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->render('ba_list', [
                    'dataProvider' => $this->fetchBankAccounts($model->org_id),
                    'model' => $this->createNewBankAccountModel($model->org_id),
                    'action' => self::URL_ADD_BANK_ACCOUNT,
                ]);
            }
        }

        return false;
    }

    /**
     * Выполняет удаление банковского счета из организации.
     * @param $id integer идентификатор банковского счета, который надо удалить
     * @return mixed
     */
    public function actionDeleteBankAccount($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = OrganizationsBas::findOne($id);
            if ($model) {
                $org_id = $model->org_id;
                $model->delete();

                return $this->render('ba_list', [
                    'dataProvider' => $this->fetchBankAccounts($org_id),
                    'model' => $this->createNewBankAccountModel($org_id),
                    'action' => self::URL_ADD_BANK_ACCOUNT,
                ]);
            }
        }

        return false;
    }
}
