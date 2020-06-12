<?php

use yii\widgets\Pjax;
use yii\helpers\Url;
use common\models\FinanceTransactions;
use backend\components\grid\TotalAmountColumn;
use ferryman\controllers\PaymentsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $queryString string */
/* @var $totalAmount float общая сумма по всем платежным ордерам (вне зависимости от номера просматриваемой страницы) */

$this->title = 'Оплаты | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Оплаты';

$this->params['breadcrumbsRight'][] = ['label' => 'Экспорт в Excel', 'icon' => 'fa fa-file-excel-o fa-lg', 'url' => Url::to(PaymentsController::URL_ROOT) . '?export=true' . $queryString, 'class' => 'btn'];
?>

<?= $this->render('_search', ['model' => $searchModel]); ?>

<div class="card">
    <div class="card-block">
        <?php Pjax::begin(['id' => 'fpo', 'enablePushState' => false]); ?>

        <?= \backend\components\grid\GridView::widget([
            'id' => 'gwPaymentOrders',
            'dataProvider' => $dataProvider,
            'showFooter' => true,
            'columns' => [
                [
                    'attribute' => 'projects',
                    'label' => 'Заказы',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PaymentOrders */
                        /* @var $column \yii\grid\DataColumn */

                        return implode(', ', explode(',', $model->{$column->attribute}));
                    },
                    'footer' => '<strong>Итого:</strong>',
                    'footerOptions' => ['class' => 'text-right'],
                ],
                [
                    'class' => TotalAmountColumn::class,
                    'attribute' => 'amount',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PaymentOrders */
                        /* @var $column \yii\grid\DataColumn */

                        return FinanceTransactions::getPrettyAmount($model->{$column->attribute}, 'html');
                    },
                    'options' => ['width' => '150'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-right'],
                    'footerOptions' => ['class' => 'text-right'],
                ],
                [
                    'attribute' => 'payment_date',
                    'format' => 'date',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'or_at',
                    'label' => 'Оригинал АВР',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PaymentOrders */
                        /* @var $column \yii\grid\DataColumn */

                        if (!empty($model->{$column->attribute})) {
                            return '<i class="fa fa-check-circle-o text-success" title="Оригинал акта выполненных работ получен ' . Yii::$app->formatter->asDate($model->payment_date, 'php:d F Y г.') . '"></i>';
                        }
                        else {
                            return '';
                        }
                    },
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
