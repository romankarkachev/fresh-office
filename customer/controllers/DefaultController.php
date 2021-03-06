<?php

namespace customer\controllers;

use common\models\MobileAppGeopos;
use common\models\ProjectsRatings;
use common\models\ProjectsStates;
use customer\models\ProjectRatingForm;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\CustomerInvitations;
use common\models\foCompany;
use common\models\foProjects;
use common\models\ProjectsTypes;
use customer\models\CustomerRequestForm;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller
 */
class DefaultController extends Controller
{
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
                        'actions' => ['login', 'error', 'project-rating'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index',
                            // форма отправки обратной связи
                            'request-form', 'send-request',
                            // форма оценки проекта
                            'rating-form', 'rate-project',
                            // транспорт на карте
                            'geopos', 'get-mobile-apps-geopositions',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'send-request' => ['post'],
                    'rate-project' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id == 'error') $this->layout ='//na';

            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Отображает Рабочий стол.
     * @return mixed
     */
    public function actionIndex()
    {
        $caData = [];
        if (!Yii::$app->user->can('root')) {
            $invitation = CustomerInvitations::findOne(['user_id' => Yii::$app->user->id]);
            if (!empty($invitation)) {
                $company = foCompany::findOne(['ID_COMPANY' => $invitation->fo_ca_id]);
                if (!empty($company)) {
                    // возможность использования функции "Нужен вывоз отходов" определяется таким методом:
                    // один последний проект по этой компании должен быть определенного типа
                    $canCreateProject = false;
                    $lastProject = foProjects::find()
                        ->where([
                            'ID_COMPANY' => $invitation->fo_ca_id,
                        ])
                        ->andWhere([
                            'or',
                            ['LIST_PROJECT_COMPANY.TRASH' => null],
                            ['LIST_PROJECT_COMPANY.TRASH' => 0],
                        ])
                        ->orderBy('DATE_CREATE_PROGECT DESC')->one();

                    if (!empty($lastProject))
                        if (in_array($lastProject->ID_LIST_SPR_PROJECT, ProjectsTypes::НАБОР_ДОПУСТИМЫХ_ТИПОВ_ПРОИЗВОДСТВО))
                            $canCreateProject = true;

                    // проекты для выставления оценки
                    $alreadyRatedProjects = ProjectsRatings::find()->select('project_id')->where(['ca_id' => $invitation->fo_ca_id])->column();
                    $ratingProjects = foProjects::find()->where([
                        'ID_COMPANY' => $invitation->fo_ca_id,
                    ])->andWhere([
                        'or',
                        ['LIST_PROJECT_COMPANY.TRASH' => null],
                        ['LIST_PROJECT_COMPANY.TRASH' => 0],
                    ])->andWhere([
                        'not in',
                        'ID_LIST_PROJECT_COMPANY',
                        $alreadyRatedProjects
                    ])->andWhere([
                        'not in',
                        'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT',
                        [ProjectsStates::STATE_ОТКАЗ_КЛИЕНТА, ProjectsStates::STATE_НЕВЕРНОЕ_ОФОРМЛЕНИЕ_ЗАЯВКИ],
                    ])->orderBy('DATE_CREATE_PROGECT DESC')->limit(3)->all();

                    $avgRating = ProjectsRatings::find()->where(['ca_id' => $invitation->fo_ca_id])->average('rate');

                    $caData = [
                        'faCaId' => $invitation->fo_ca_id,
                        'balance' => intval($company->balanceForCustomer),
                        'contractNum' => $company->ADD_numb_dogovor,
                        'contractExpiredAt' => $company->ADD_date_finish,
                        'responsibleManagerName' => $company->managerName,
                        'responsibleManagerPhone' => $company->managerPhone,
                        'responsibleManagerEmail' => $company->managerEmail,
                        'contacts' => $company->tasksInProgress,
                        'canCreateProject' => $canCreateProject,
                        'ratingProjects' => $ratingProjects,
                        'avgRating' => $avgRating,
                    ];
                }
            }
        }

        return $this->render('index', [
            'caData' => $caData,
        ]);
    }

    /**
     * Формирует и отдает форму обращения заказчика в нашу компанию.
     * @param $type integer тип обращения
     * @return mixed
     */
    public function actionRequestForm($type)
    {
        if (Yii::$app->request->isAjax) {
            $model = new CustomerRequestForm();
            $model->type = intval($type);

            return $this->renderAjax('_request_form', [
                'model' => $model,
            ]);
        }

        return '';
    }

    /**
     * Выполняет отправку обращения заказчика в нашу компанию.
     */
    public function actionSendRequest()
    {
        $model = new CustomerRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->sendRequest();
            $this->redirect(['/']);
        }
    }

    /**
     * Формирует и отдает форму обращения заказчика в нашу компанию.
     * @param $project_id integer идентификатор проекта
     * @param $ca_id integer идентификатор контрагента
     * @param $rate integer выставленная пользователем оценка
     * @return mixed
     */
    public function actionRatingForm($project_id, $ca_id, $rate)
    {
        if (Yii::$app->request->isAjax) {
            $model = new ProjectRatingForm([
                'ca_id' => $ca_id,
                'project_id' => $project_id,
                'rate' => $rate,
            ]);

            return $this->renderAjax('_rate_form', [
                'model' => $model,
            ]);
        }

        return '';
    }

    /**
     * Выполняет отправку обращения заказчика в нашу компанию.
     */
    public function actionRateProject()
    {
        $model = new ProjectRatingForm();
        if (Yii::$app->request->isPost)
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                return $model->rateProject();
            }
            else {
                $model = new ProjectRatingForm([
                    'ca_id' => intval(Yii::$app->request->post('ca_id')),
                    'project_id' => intval(Yii::$app->request->post('project_id')),
                    'rate' => intval(Yii::$app->request->post('rate')),
                ]);
                return $model->rateProject();
            }
    }

    /**
     * Отображает на карте автомобили, следующие к производственной площадке. У них должно быть запущено мобильное приложение.
     * @return mixed
     */
    public function actionGeopos()
    {
        return $this->render('@backend/views/freights-on-the-way/geopos', [
            'urlGetGeopositions' => \yii\helpers\Url::to(['/get-mobile-apps-geopositions']),
        ]);
    }

    /**
     * Делает выборку местоположений пользователей мобильного приложения.
     * get-mobile-apps-geopositions
     */
    public function actionGetMobileAppsGeopositions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $timeLimit = time() - 15 * 60; // только свежие координаты, полученные не позднее 15 минут назад
        $result = MobileAppGeopos::find()->select([
            MobileAppGeopos::tableName() . '.user_id',
            'userProfileName' => 'profile.name',
            'markerTitle' => 'CONCAT(`profile`.`name`, \' \', FROM_UNIXTIME(`arrived_at`, \'%d.%m.%Y в %H:%i:%s\'))',
            'coord_lat',
            'coord_long',
        ])
            ->distinct()
            ->joinWith(['userProfile'], false)
            ->where('arrived_at > ' . $timeLimit)
            ->asArray()->all();

        return $result;
    }

    /**
     * project-rating
     * @return mixed
     * @throws NotFoundHttpException если инструмент для голосования отсуствует по какой-то причине
     * @throws BadRequestHttpException
     */
    public function actionProjectRating()
    {
        $this->layout = '//na';
        if (Yii::$app->request->isPost) {
            $model = new ProjectRatingForm();
            if ($model->load(Yii::$app->request->post())) {
                $projectRating = ProjectsRatings::findOne(['project_id' => $model->project_id]);
                if ($projectRating) {
                    $projectRating->updateAttributes([
                        'rate' => $model->rate,
                        'comment' => $model->comment,
                    ]);

                    return $this->render('_na_rate_end');
                }
            }
        }
        else {
            // уникальный идентификатор, по которому происходит позиционирование на голосование
            if (Yii::$app->request->get('token') !== null) {
                $token = Yii::$app->request->get('token');
                $projectRating = ProjectsRatings::findOne(['token' => $token]);
                if ($projectRating !== null) {
                    if (empty($projectRating->rate)) {
                        $model = new ProjectRatingForm([
                            'ca_id' => $projectRating->ca_id,
                            'project_id' => $projectRating->project_id,
                        ]);
                        if (Yii::$app->request->get('rate') !== null) {
                            $model->rate = Yii::$app->request->get('rate');
                            // сразу сохраним оценку пользователя
                            $projectRating->updateAttributes([
                                'rate' => $model->rate,
                            ]);
                        }

                        return $this->render('_na_rate_form', [
                            'model' => $model,
                        ]);
                    }
                    else {
                        return $this->render('_na_rate_end');
                    }
                }
                else {
                    throw new NotFoundHttpException('Запрошенная страница не существует.');
                }
            }
            else {
                throw new BadRequestHttpException('Неверно заданы параметры.');
            }
        }
    }
}
