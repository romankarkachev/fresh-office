<?php

namespace backend\controllers;

use Yii;
use common\models\pbxWebsites;
use common\models\pbxWebsitesSearch;
use common\models\pbxExternalPhoneNumber;
use common\models\pbxExternalPhoneNumberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * PbxWebsitesController implements the CRUD actions for pbxWebsites model.
 */
class PbxWebsitesController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'pbx-websites';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Сайты';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для обработки формы добавления нового номера телефона
     */
    const URL_ADD_EXTERNAL_PHONE = 'add-phone-number';

    /**
     * URL для обработки формы добавления нового номера телефона в виде массива
     */
    const URL_ADD_EXTERNAL_PHONE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_ADD_EXTERNAL_PHONE];

    /**
     * URL для удаления номера телефона сайта
     */
    const URL_DELETE_EXTERNAL_PHONE = 'delete-phone-number';

    /**
     * URL для удаления номера телефона сайта в виде массива
     */
    const URL_DELETE_EXTERNAL_PHONE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_EXTERNAL_PHONE];

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
                            'external-phones-list', self::URL_ADD_EXTERNAL_PHONE, self::URL_DELETE_EXTERNAL_PHONE,
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
                    self::URL_DELETE_EXTERNAL_PHONE => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку номеров телефонов сайта, переданного в параметрах.
     * @param $model pbxWebsites
     * @return \yii\data\ActiveDataProvider
     */
    private function fetchExternalPhones($model)
    {
        $searchModel = new pbxExternalPhoneNumberSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'website_id' => $model->id,
            ]
        ]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Lists all pbxWebsites models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new pbxWebsitesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new pbxWebsites model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new pbxWebsites();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/' . self::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing pbxWebsites model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(self::ROOT_URL_AS_ARRAY);
        } else {

        }

        $newPhoneModel = new pbxExternalPhoneNumber();
        $newPhoneModel->website_id = $model->id;

        return $this->render('update', [
            'model' => $model,
            'dpPhones' => $this->fetchExternalPhones($model),
            'newPhoneModel' => $newPhoneModel,
        ]);
    }

    /**
     * Deletes an existing pbxWebsites model.
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
                    'breadcrumbs' => ['label' => self::MAIN_MENU_LABEL, 'url' => self::ROOT_URL_AS_ARRAY],
                    'modelRep' => $model->name,
                    'buttonCaption' => self::MAIN_MENU_LABEL,
                    'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                ],
            ]);

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the pbxWebsites model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return pbxWebsites the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = pbxWebsites::findOne($id)) !== null) {
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
    public function actionExternalPhonesList($id)
    {
        $newMessage = new pbxExternalPhoneNumber();
        $newMessage->website_id = $id;

        return $this->render('external_phones_list', [
            'dataProvider' => $this->fetchExternalPhones($this->findModel($id)),
            'model' => $newMessage,
            'action' => self::URL_ADD_EXTERNAL_PHONE,
        ]);
    }

    /**
     * Выполняет интерактивное добавление номера телефона к сайту.
     * @return mixed
     */
    public function actionAddPhoneNumber()
    {
        $model = new pbxExternalPhoneNumber();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->render('external_phones_list', [
                'dataProvider' => $this->fetchExternalPhones($model->website),
                'model' => new pbxExternalPhoneNumber([
                    'website_id' => $model->website->id,
                ]),
                'action' => self::URL_ADD_EXTERNAL_PHONE,
            ]);
        }
    }

    /**
     * Выполняет удаление номера телефона сайта.
     * @param $id integer идентификатор номера телефона сайта, который надо удалить
     * @return mixed
     */
    public function actionDeletePhoneNumber($id)
    {
        $model = pbxExternalPhoneNumber::findOne($id);
        if ($model) {
            $website = $model->website;
            $model->delete();

            return $this->render('external_phones_list', [
                'dataProvider' => $this->fetchExternalPhones($website),
                'model' => new pbxExternalPhoneNumber([
                    'website_id' => $website->id,
                ]),
                'action' => self::URL_ADD_EXTERNAL_PHONE,
            ]);
        }
    }
}
