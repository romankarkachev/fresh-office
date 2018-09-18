<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $totalAmount float общая сумма по всем платежным ордерам (вне зависимости от номера просматриваемой страницы) */
?>

<div class="panel panel-info">
    <div class="panel-body table-responsive">
        <?php Pjax::begin(['id' => 'fpo', 'enablePushState' => false]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'gw-fpo',
            //'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small>\n{pager}</div>\n{items}",
            //'summary' => "Показаны записи с <strong>{begin}</strong> по <strong>{end}</strong>, на странице <strong>{count}</strong>, всего <strong>{totalCount}</strong>. Страница <strong>{page}</strong> из <strong>{pageCount}</strong>.",
            'layout' => '{pager}{items}<div style="float:left;">{summary}</div><div style="float:right;">Общая сумма: <strong>' . Yii::$app->formatter->asCurrency($totalAmount) . '</strong></div>',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                [
                    'attribute' => 'created_at',
                    'label' => 'Создано',
                    'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'options' => ['width' => '130'],
                ],
                'createdByProfileName',
                [
                    'attribute' => 'stateName',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PaymentOrders */
                        /* @var $column \yii\grid\DataColumn */

                        if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ)
                            return Html::tag('abbr', $model->{$column->attribute}, ['title' => $model->comment]);
                        else
                            return $model->{$column->attribute};
                    },
                ],
                [
                    'attribute' => 'projects',
                    'format' => 'raw',
                    'options' => ['width' => '200'],
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PaymentOrders */
                        /* @var $column \yii\grid\DataColumn */

                        return implode(', ', explode(',', $model->{$column->attribute}));
                    },
                ],
                [
                    'attribute' => 'cas',
                    'label' => 'Контрагенты',
                ],
                [
                    'attribute' => 'vds',
                    'label' => 'Вывоз',
                ],
                [
                    'attribute' => 'amount',
                    'format' => 'currency',
                    'options' => ['width' => '100'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-right'],
                ],
                [
                    'attribute' => 'payment_date',
                    'format' => 'date',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>