<?php

namespace backend\controllers;

use Yii;
use common\models\NotifReceiversStatesNotChangedByTime;
use common\models\NotifReceiversStatesNotChangedByTimeSearch;
use common\models\CorrespondencePackagesStates;
use common\models\foProjectsStates;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Справочник для хранения получателей уведомлений по E-mail сведений о проектах, статусы которых не меняются в течение заданного администратором времени.
 */
class NotificationsReceiversSncbtController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'notifications-receivers-sncbt';

    /**
     * URL для подбора отходов по коду ФККО
     */
    const URL_RENDER_STATE_BLOCK = 'render-state-block';
    const URL_RENDER_STATE_BLOCK_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_STATE_BLOCK];

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
                        'actions' => ['index', 'create', 'update', 'delete', self::URL_RENDER_STATE_BLOCK],
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
     * Lists all NotifReceiversStatesNotChangedByTime models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NotifReceiversStatesNotChangedByTimeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statesProjects' => foProjectsStates::arrayMapForSelect2(),
            'statesCp' => CorrespondencePackagesStates::arrayMapForSelect2(),
        ]);
    }

    /**
     * Creates a new NotifReceiversStatesNotChangedByTime model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new NotifReceiversStatesNotChangedByTime();
        $params = ['model' => $model];

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['/notifications-receivers-sncbt']);
            }
        }
        else {
            $model->section = NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ;
        }

        switch ($model->section) {
            case NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ:
                $params['states'] = foProjectsStates::arrayMapForSelect2();
                break;
            case NotifReceiversStatesNotChangedByTime::SECTION_ПАКЕТЫ:
                $params['states'] = CorrespondencePackagesStates::arrayMapForSelect2();
                break;
        }

        return $this->render('create', $params);
    }

    /**
     * Updates an existing NotifReceiversStatesNotChangedByTime model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $time = 0;
        $model = $this->findModel($id);
        $params = ['model' => $model];

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(['/notifications-receivers-sncbt']);
        }
        else {
            $time = $model->time;
            $model->time /= 60;
            $model->periodicity = NotifReceiversStatesNotChangedByTime::PERIOD_MINUTE;
        }

        $params['time'] = $time;

        switch ($model->section) {
            case NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ:
                $params['states'] = foProjectsStates::arrayMapForSelect2();
                break;
            case NotifReceiversStatesNotChangedByTime::SECTION_ПАКЕТЫ:
                $params['states'] = CorrespondencePackagesStates::arrayMapForSelect2();
                break;
        }

        return $this->render('update', $params);
    }

    /**
     * Deletes an existing NotifReceiversStatesNotChangedByTime model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/notifications-receivers-sncbt']);
    }

    /**
     * Finds the NotifReceiversStatesNotChangedByTime model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NotifReceiversStatesNotChangedByTime the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NotifReceiversStatesNotChangedByTime::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Рендерит поле "Статус" в зависимости от раздела учета.
     * render-state-block
     * @param $section int раздел учета
     * @return mixed
     */
    public function actionRenderStateBlock($section)
    {
        switch ($section) {
            case NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ:
                $states = foProjectsStates::arrayMapForSelect2();
                break;
            case NotifReceiversStatesNotChangedByTime::SECTION_ПАКЕТЫ:
                $states = CorrespondencePackagesStates::arrayMapForSelect2();
                break;
        }

        if (!empty($states)) return $this->renderAjax('_field_state', ['model' => new NotifReceiversStatesNotChangedByTime(['section' => $section]), 'form' => \yii\bootstrap\ActiveForm::begin(), 'states' => $states]);
    }
}
