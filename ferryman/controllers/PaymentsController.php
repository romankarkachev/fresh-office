<?php

namespace ferryman\controllers;

use Yii;
use common\models\PaymentOrdersSearch;
use common\models\Ferrymen;
use common\models\foProjects;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use moonland\phpexcel\Excel;

/**
 * PaymentsController implements the CRUD actions for PaymentOrders model.
 */
class PaymentsController extends Controller
{
    /**
     * URL корня списка
     */
    const URL_ROOT = 'payments';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['root', 'ferryman'],
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
     * Страница "Платежные ордеры".
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new PaymentOrdersSearch();
        $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
        // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
        if ($ferryman == null) {
            $dataProvider = new ActiveDataProvider(['query' => foProjects::find()->where('1 <> 1')]);
        }
        else {
            $dataProvider = $searchModel->search(ArrayHelper::merge([
                $searchModel->formName() => [
                    'ferryman_id' => $ferryman->id,
                ],
            ], Yii::$app->request->queryParams), self::URL_ROOT);
            if ($dataProvider instanceof ActiveDataProvider) {
                $dataProvider->pagination = false;
                $dataProvider->sort->defaultOrder = ['payment_date' => SORT_DESC];
            }
        }

        if (Yii::$app->request->get('export') != null) {
            if ($dataProvider->getTotalCount() == 0) {
                Yii::$app->getSession()->setFlash('error', 'Нет данных для экспорта.');
                $this->redirect(Url::to());
                return false;
            }

            // именно для экспорта постраничный переход отключается, чтобы в файл выгружались все записи
            $dataProvider->pagination = false;
            Excel::export([
                'models' => $dataProvider->getModels(),
                'fileName' => 'Платежные ордеры (сформирован '.date('Y-m-d в H i').').xlsx',
                'asAttachment' => true,
                'columns' => [
                    [
                        'attribute' => 'projects',
                        'label' => '№№ заказов',
                    ],
                    [
                        'attribute' => 'amount',
                        'label' => 'Суммма',
                        'format' => 'currency',
                    ],
                    [
                        'attribute' => 'payment_date',
                        'label' => 'Дата оплаты',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'or_at',
                        'label' => 'Оригинал АВР',
                        'format' => 'date',
                    ],
                ],
            ]);
        }
        else {
            // в кнопку Экспорт в Excel встраиваем строку запроса
            $queryString = '';
            if (Yii::$app->request->queryString != '') $queryString = '&' . Yii::$app->request->queryString;

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'queryString' => $queryString,
            ]);
        }
    }
}
