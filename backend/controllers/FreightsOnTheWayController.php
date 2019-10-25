<?php

namespace backend\controllers;

use Yii;
use common\models\foProjectsSearch;
use common\models\Ferrymen;
use common\models\foProjects;
use common\models\foProjectsHistory;
use common\models\MobileAppGeopos;
use common\models\ProjectsStates;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Отображает электронную очередь транспорта, находящегося на пути к производственным площадкам.
 */
class FreightsOnTheWayController extends Controller
{
    /**
     * Время в течение которого координаты пользователей мобильного приложения считаются свежими.
     * Задается в минутах.
     */
    const TIME_WHEN_GEOPOS_IS_FRESH = 15;

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
                        'actions' => ['index', 'get-duration-for-route', 'geopos', 'get-mobile-apps-geopositions'],
                        'allow' => true,
                        'roles' => ['root', 'logist', 'prod_department_head'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку устаревших местоположений пользователей.
     * @return yii\data\ActiveDataProvider
     */
    private function fetchOutdatedGeopositions()
    {
        return new \yii\data\ActiveDataProvider([
            'query' => MobileAppGeopos::find()
                ->distinct()
                ->joinWith(['userProfile'], false)
                ->where('arrived_at <= ' . (time() - self::TIME_WHEN_GEOPOS_IS_FRESH * 60))
                ->orderBy('arrived_at DESC'),
            'pagination' => false,
            'sort' => false,
        ]);
    }

    /**
     * Отображает электронную очередь автомобилей, следующих к производственной площадке.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->searchForFreightsOnTheWay();

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Отображает на карте автомобили, следующие к производственной площадке. У них должно быть запущено мобильное приложение.
     * @return mixed
     */
    public function actionGeopos()
    {
        return $this->render('geopos', [
            'urlGetGeopositions' => \yii\helpers\Url::to(['/freights-on-the-way/get-mobile-apps-geopositions']),
            'dpOutdated' => $this->fetchOutdatedGeopositions(),
        ]);
    }

    /**
     * @param $project_id integer идентификатор проекта, адрес которого вычисляется
     * @param $iterator integer номер строки таблицы на странице, в которой будут обновляться данные
     * @return mixed
     */
    public function actionGetDurationForRoute($project_id, $iterator)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $project = foProjects::find()->where(['ID_LIST_PROJECT_COMPANY' => $project_id])->one();
            if ($project) {
                $depaturedAt = 0;
                $unloading_time = 0;
                $remain_text = '';
                $remain_value = '';
                $arriving_at = '';
                $unload_at = PHP_INT_MAX;

                // определим дату приобретения статуса "Едет на склад"
                $state = foProjectsHistory::findOne(['ID_LIST_PROJECT_COMPANY' => $project_id, 'ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_ЕДЕТ_НА_СКЛАД]);
                if ($state) {
                    $depaturedAt = $state->DATE_CHENCH_PRIZNAK;
                }

                // если в проекте заполнен адрес, то отправим его и производственную площадку на Google, чтобы определить время в пути
                if (!empty($project->ADD_adres)) {
                    $client = new Client();
                    $response = $client->createRequest()
                        ->setMethod('get')
                        ->setUrl('https://maps.googleapis.com/maps/api/directions/json')
                        ->setData([
                            'language' => 'ru',
                            'origin' => $project->ADD_adres,
                            'destination' => $project->ADD_proizodstvo,
                            'key' => 'AIzaSyDdvRkgkcmWz-60NfeTCthFIeEq9Yy_liI',
                        ])
                        ->send();

                    if ($response->isOk) {
                        $data = $response->getData();
                        $remain_text = $data['routes'][0]['legs'][0]['duration']['text'];
                        $remain_value = $data['routes'][0]['legs'][0]['duration']['value'];

                        // попытаемся определить перевозчика, по перевозчику - транспорт, из транспорта узнаем его тип,
                        // а в типе хранится время на разгрузку
                        try {
                            $ferryman = Ferrymen::findOne(['name_crm' => $project->ADD_perevoz]);
                            if ($ferryman) {
                                // перевозчик идентифицирован, теперь попробуем найти у него такой транспорт
                                foreach ($ferryman->transport as $transport) {
                                    $data = str_replace(chr(32), '', mb_strtolower($project->ADD_dannie));
                                    if (false !== stripos($data, $transport->rn_index)) {
                                        // транспорт такой наден, зафиксируем время на разгрузку по его типу
                                        if (!empty($transport->tt)) $unloading_time = $transport->tt->unloading_time;
                                        break;
                                    }
                                }
                            }
                        }
                        catch (\Exception $exception) {}

                        $arriving_at = strtotime($depaturedAt) + $remain_value;
                        $unload_at = $arriving_at + ($unloading_time * 60);
                    }
                }

                return [
                    'iterator' => $iterator,
                    'project_id' => $project_id,
                    'remain_text' => $remain_text,
                    'remain_value' => $remain_value,
                    'unloading_time' => $unloading_time,
                    'depatured_at' => strtotime($depaturedAt),
                    'arriving_at' => !empty($arriving_at) ? Yii::$app->formatter->asDate($arriving_at, 'php:d.m.Y H:i') : '',
                    'unload_at' => !empty($unload_at) ? Yii::$app->formatter->asDate($unload_at, 'php:d.m.Y H:i') : '',
                    'unload_sort' => $unload_at,
                ];
            }
        }

        return false;
    }

    /**
     * Делает выборку местоположений пользователей мобильного приложения.
     * get-mobile-apps-geopositions
     */
    public function actionGetMobileAppsGeopositions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $timeLimit = time() - self::TIME_WHEN_GEOPOS_IS_FRESH * 60; // только свежие координаты, полученные не позднее 15 минут назад
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
}
