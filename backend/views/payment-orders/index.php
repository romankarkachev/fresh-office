<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PaymentOrdersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Платежные ордеры | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Платежные ордеры';
?>
<div class="payment-orders-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
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
            'ferrymanName',
            'projects',
            // 'pd_type',
            // 'pd_id',
            'amount:currency',
            'payment_date:date',
            [
                'label' => 'Быстрая реакция',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\PaymentOrders */
                    /* @var $column \yii\grid\DataColumn */

                    $buttons = '';

                    // кнопки логиста
                    if (Yii::$app->user->can('logist') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК)
                        $buttons = Html::a('На согласование', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'changeOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ . $model->id,
                            'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ,
                        ]);

                    // кнопки руководителя
                    if (Yii::$app->user->can('root') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ)
                        $buttons = Html::a('Согласовать', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'changeOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН . $model->id,
                            'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН,
                        ]);

                    // кнопки бухгалтера
                    if (Yii::$app->user->can('accountant') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН)
                        $buttons = Html::a('Отметить оплаченным', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'changeOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН . $model->id,
                            'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН,
                        ]);

                    return '<div id="block-state' . $model->id . '">' . $buttons . '</div>';
                },
                'options' => ['width' => '200'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<?php
$url = Url::to(['/payment-orders/change-state-on-the-fly']);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопкам модерации.
//
function btnchangeOnTheFlyOnClick() {
    id = $(this).attr("data-id");
    state = $(this).attr("data-state");
    if (id != undefined && id != "" && state != undefined && state != "") {
        \$block = $("#block-state" + id).html("<i class=\"fa fa-cog fa-spin text-muted\"></i>");
        $.post("$url?po_id=" + id + "&state_id=" + state, function(data) {
            if (data == true)
                \$block.html('<i class="fa fa-check-circle-o text-success"></i>');
            else
                \$block.html('<i class="fa fa-times text-danger"></i>');
        });
    }

    return false;
} // btnchangeOnTheFlyOnClick()

$(document).on("click", "a[id ^= 'changeOnTheFly']", btnchangeOnTheFlyOnClick);
JS
, yii\web\View::POS_READY);
?>