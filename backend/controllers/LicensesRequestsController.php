<?php

namespace backend\controllers;

use Yii;
use common\models\LicensesRequests;
use common\models\LicensesRequestsSearch;
use common\models\LicensesRequestsStates;
use common\models\LicensesRequestsFkko;
use common\models\LicensesRequestsFkkoSearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;

/**
 * LicensesRequestsController implements the CRUD actions for LicensesRequests model.
 */
class LicensesRequestsController extends Controller
{
    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'licenses-requests';

    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL для создания записи
     */
    const URL_CREATE = 'create';

    /**
     * URL для создания записи в виде массива
     */
    const URL_CREATE_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE];

    /**
     * URL для дублирования записи
     */
    const URL_COPY = 'copy';

    /**
     * URL для дублирования записи в виде массива
     */
    const URL_COPY_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_COPY];

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
                        'actions' => ['index', 'create', 'fkko-list'],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_head', 'sales_department_manager', 'dpc_head', 'tenders_manager'],
                    ],
                    [
                        'actions' => [self::URL_CREATE, self::URL_COPY, 'update', 'wizard'],
                        'allow' => true,
                        'roles' => ['root', 'sales_department_head', 'sales_department_manager'],
                    ],
                    [
                        'actions' => ['delete'],
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
     * Делает выборку кодов ФККО, которые относятся к этому запросу и возвращает dataProvider.
     * @param $id integer идентификатор запроса лицензии
     * @return \yii\data\ActiveDataProvider
     */
    private function fkkoDataProvider($id)
    {
        $searchModel = new LicensesRequestsFkkoSearch();
        return $searchModel->search([
            $searchModel->formName() => [
                'lr_id' => $id,
            ],
        ], $id);
    }

    /**
     * Формирует PDF из файлов сканов лицензии и возвращает путь к сформированному файлу.
     * @param $lr_id integer идентификатор запроса
     * @return mixed
     */
    private function generatePdf($lr_id)
    {
        $files = LicensesRequestsFkko::find()
            ->groupBy('file_id')
            ->where(['lr_id' => $lr_id])
            ->all();

        $content = '';
        foreach ($files as $file) {
            $content .= $this->renderPartial('@common/mail/_licensesScans', ['file' => $file, 'lr_id' => $lr_id]);
        }

        $filepath = Yii::getAlias('@uploads-temp-pdfs');
        $filename = $filepath . '/lr-' . $lr_id . '.pdf';
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'destination' => Pdf::DEST_FILE,
            'filename' => $filename,
            'content' => $content,
            // поля
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 10,
            'marginBottom' => 10,
        ]);

        try {
            $pdf->render();
            return $filename;
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Lists all LicensesRequests models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LicensesRequestsSearch();
        $conditions = Yii::$app->request->queryParams;
        if (!Yii::$app->user->can('root') && !Yii::$app->user->can('sales_department_head')) {
            // для менеджеров отбор только по созданным ими запросам
            $conditions[$searchModel->formName()]['created_by'] = Yii::$app->user->id;
        }
        $dataProvider = $searchModel->search($conditions);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => !empty(Yii::$app->request->get($searchModel->formName())),
        ]);
    }

    /**
     * Рендерит только табличную часть запроса лицензии.
     * В принципе для pjax только и используется.
     * @param $id integer идентификатор запроса, таблична часть которого будет извлекаться
     * @return mixed
     */
    public function actionFkkoList($id)
    {
        return $this->render('_fkko', [
            'dataProvider' => $this->fkkoDataProvider($id),
        ]);
    }

    /**
     * Creates a new LicensesRequests model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new LicensesRequests();
        $model->state_id = LicensesRequestsStates::LICENSE_STATE_НОВЫЙ;

        if ($model->load(Yii::$app->request->post())) {
            // если файл по сформированному пути удалось загрузить на сервер и успешно на нем сохранить
            if ($model->validate() && $model->save()) {
                // если удалось сохранить модель, то создадим необходимое количество записей кодов ФККО
                foreach ($model->tpFkkos as $fkko) {
                    /* @var $fkko LicensesRequestsFkko */
                    $fkko->lr_id = $model->id;
                    $fkko->save();
                }

                // сохраним E-mail, который ввел менеджер в его профиле
                if (!empty(Yii::$app->user->identity->profile)) {
                    if (empty(Yii::$app->user->identity->profile->public_email) || Yii::$app->user->identity->profile->public_email != $model->receivers_email) {
                        Yii::$app->user->identity->profile->public_email = $model->receivers_email;
                        Yii::$app->user->identity->profile->save();
                    }
                }

                if (Yii::$app->user->can('root') || Yii::$app->user->can('sales_department_head'))
                    return $this->redirect(['/licenses-requests']);
                else {
                    Yii::$app->session->setFlash('success', 'Запрос на лицензию был успешно сформирован.');
                    return $this->redirect(['/licenses-requests/create']);
                }
            }
        }
        else {
            // будем использовать E-mail из профиля пользователя либо из учетной записи, если в профиле нет
            $email = Yii::$app->user->identity->email;
            if (!empty(Yii::$app->user->identity->profile) && !empty(Yii::$app->user->identity->profile->public_email)) {
                $email = Yii::$app->user->identity->profile->public_email;
            }
            $model->receivers_email = $email;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Выполняет копирование записи.
     * @param $id integer идентификатор записи-источника
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCopy($id)
    {
        $model = new LicensesRequests();
        $origin = $this->findModel($id);
        if ($origin != null) {
            $model->attributes = $origin->attributes;
            $model->fkkosTextarea = $origin->licensesRequestsFkkosTextarea;

            return $this->render('create', [
                'model' => $model,
                'formAction' => Url::to(self::URL_CREATE_AS_ARRAY),
            ]);
        }

        Yii::$app->session->setFlash('error', 'Запись-источник не обнаружена. Создание копии невозможно.');
        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Updates an existing LicensesRequests model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $state = $model->state_id;
            if (Yii::$app->request->post('reject') !== null) {
                $model->state_id = LicensesRequestsStates::LICENSE_STATE_ОТКАЗ;
                $body = 'По Вашему запросу лицензии был получен отказ. Причина:<br />' . $model->comment;
            }
            else if (Yii::$app->request->post('allow') !== null) {
                $model->state_id = LicensesRequestsStates::LICENSE_STATE_ОДОБРЕН;
                $body = 'Ваш запрос лицензии для компании <strong>' . $model->ca_name . '</strong> был одобрен, файл с необходимыми сканами находится во вложении.';
            }

            if ($model->save(true, ['state_id', 'comment'])) {
                $letter = Yii::$app->mailer->compose([
                    'html' => 'licenseRequest-html',
                ], [
                    'body' => $body,
                ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameSvetozar']])
                    // ранее была отправка на E-mail автора, теперь отправка идет на E-mail, указанный менеджером вручную
                    //->setTo($model->createdByEmail)
                    ->setTo($model->receivers_email)
                    ->setSubject('Сканы лицензии по запросу № ' . $model->id . ' от ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y H:i'));

                $pdfFfp = '';
                if ($model->state_id == LicensesRequestsStates::LICENSE_STATE_ОДОБРЕН) {
                    // формируем сам файл с вложенными в него сканами лицензий
                    $pdfFfp = $this->generatePdf($id);
                    $letter->attach($pdfFfp);
                }

                $letter->send();

                // удаляем возможный временный файл
                if (!empty($pdfFfp) && file_exists($pdfFfp)) unlink($pdfFfp);

                if (Yii::$app->request->get('is_wizard')) {
                    // если идет работа мастера, то возвращаем пользователя в режим обработки запросов через мастер
                    Yii::$app->session->setFlash('success', 'Запрос лицензии №' . $model->id . ' для компании ' . $model->ca_name . ' обработан.');
                    return $this->redirect(['/licenses-requests/wizard']);
                }
                else
                    // иначе просто в список запросов лицензий
                    return $this->redirect(['/licenses-requests']);
            }
            else $model->state_id = $state; // возвращаем первоначальный статус запроса
        }

        $params = [
            'model' => $model,
            // табличная часть запроса (коды ФККО)
            'dataProvider' => $this->fkkoDataProvider($id),
        ];

        // если редактирование запрашивалось из мастера, то дополним URL соответствующей пометкой
        if (Yii::$app->request->get('is_wizard')) {
            $params['is_wizard'] = true;
        }

        return $this->render('update', $params);
    }

    /**
     * Deletes an existing LicensesRequests model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/licenses-requests']);
    }

    /**
     * Finds the LicensesRequests model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return LicensesRequests the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LicensesRequests::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Мастер обработки запросов лицензий.
     * Вызывает редактирование необработанных запросов лицензий до тех пор, пока новая выборка не окажется пустой.
     * @return string
     */
    public function actionWizard()
    {
        $model = LicensesRequests::find()
            ->where(['state_id' => LicensesRequestsStates::LICENSE_STATE_НОВЫЙ])
            ->orderBy('created_at')
            ->one();
        if ($model == null) return $this->render('wizard_empty_dataset');

        return $this->render('update', [
            'model' => $model,
            'is_wizard' => true,
            // табличная часть запроса (коды ФККО)
            'dataProvider' => $this->fkkoDataProvider($model->id),
        ]);
    }
}
