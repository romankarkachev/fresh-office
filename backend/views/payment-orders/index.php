<?php

use backend\controllers\PaymentOrdersController;
use common\models\PaymentOrders;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\components\TotalsColumn;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PaymentOrdersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Платежные ордеры | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Платежные ордеры';

// вычислим количество дней для оплаты перевозчикам из настроек
if (!empty(Yii::$app->params['payment_orders.days_to_pay'])) {
    $days = Yii::$app->params['payment_orders.days_to_pay'];
}
else {
    $days = 10;
}
?>
<div class="payment-orders-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o"></i> Импорт ордеров', ['import'], ['class' => 'btn btn-default pull-right']) ?>

        <?= Html::a('<i class="fa fa-close"></i> Удалить черновики', ['drop-drafts'], [
            'class' => 'btn btn-danger pull-right',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить все черновые платежные ордеры из системы?',
                'method' => 'post',
            ]
        ]) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => function ($model, $key, $index, $grid) use ($days) {
            /* @var $model \common\models\PaymentOrders */

            $options = [];
            switch ($model->state_id) {
                case PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ:
                    $options = ['class' => 'danger'];
                    break;
                case PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН:
                    $options = ['class' => 'success'];
                    break;
            }

            // если поле "Срок оплаты" заполнено, проверим, не просрочена ли оплата
            if (!empty($model->pay_till)) {
                if (time() > strtotime($model->pay_till . ' + ' . $days . ' days')) {
                    $options = ['style' => 'background-color: #fdb7ed; color: #f9f9f9;'];
                }
                elseif (time() >= strtotime($model->pay_till)) {
                    $options = ['style' => 'background-color: #f7e2f2;'];
                }
            }

            return $options;
        },
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'showFooter' => true,
        'footerRowOptions' => ['class' => 'text-right'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создано',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($days) {
                    /* @var $model \common\models\PaymentOrders */
                    /* @var $column \yii\grid\DataColumn */
                    $content = '';

                    if (!empty($model->pay_till)) {
                        $payTill = date('Y-m-d', strtotime($model->pay_till. ' + ' . $days . ' days'));
                        $content = '<br /><strong>' . Yii::$app->formatter->asDate($payTill, 'php:d.m.Y') . '</strong> <i class="fa fa-exclamation-triangle text-danger" aria-hidden="true" title="Крайний срок оплаты по данному перевозчику"></i>';
                    }

                    return Yii::$app->formatter->asDate($model->{$column->attribute}, 'php:d.m.Y H:i') . $content;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\PaymentOrders */
                    /* @var $column \yii\grid\DataColumn */

                    $result = ['class' => 'text-center'];
                    if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН && empty($model->ccp_at)) {
                        // если поле "Дата и время загрузки в систему Акта выполненных работ" заполнено, то отметим
                        $result['style'] = 'background-color:#f38f8f;color:#f9f9f9;';
                        $result['title'] = 'К платежному ордеру не прикреплен акт выполненных работ!';
                    }

                    return $result;
                },
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
                'attribute' => 'ferrymanName',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\PaymentOrders */
                    /* @var $column \yii\grid\DataColumn */

                    return Html::a($model->ferrymanName, ['/ferrymen/update', 'id' => $model->ferryman_id], ['title' => 'Открыть в новом окне', 'target' => '_blank']);
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
                'footer' => '<strong>Итого:</strong>',
                'footerOptions' => ['class' => 'text-right'],
            ],
            [
                'class' => TotalsColumn::className(),
                'attribute' => 'amount',
                'format' => 'currency',
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'payment_date',
                'format' => 'date',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'Быстрая реакция',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\PaymentOrders */
                    /* @var $column \yii\grid\DataColumn */

                    $buttons = '';

                    // кнопки логиста
                    if ((Yii::$app->user->can('logist') || Yii::$app->user->can('accountant')) && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК)
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
                    if ((Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН)
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
                'template' => '{cc_provided} {update} {delete}',
                'buttons' => [
                    'cc_provided' => function ($url, $model) {
                        /* @var $model \common\models\PaymentOrders */

                        if (empty($model->ccp_at) && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН && $model->paymentOrdersFilesCount >= 2) {
                            return Html::a('<i class="fa fa-certificate text-warning"></i>', '#', ['id' => 'btnSetCcProvidedOnTheFly' . $model->id, 'data-id' => $model->id, 'title' => 'Установить отметку наличия Акта выполненных работ', 'class' => 'btn btn-default btn-xs']);
                        }
                        else {
                            return '';
                        }
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-default btn-xs']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-danger btn-xs', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '90'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<?php
$url = Url::to(['/payment-orders/change-state-on-the-fly']);
$urlCcProvided = Url::to(PaymentOrdersController::URL_SET_CCP_ON_THE_FLY_AS_ARRAY);

$this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    \$button = $("#btnSearch");
    \$button.attr("disabled", "disabled");
    text = \$button.text();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.text(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);

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

// Обработчик щелчка по кнопкам, позволяющим отметить признак наличия актов выполненных работ.
//
function btnSetCcProvidedOnTheFlyOnClick() {
    \$button = $(this);
    id = $(this).attr("data-id");
    if (id) {
        $.post("$urlCcProvided?id=" + id, function(data) {
            var response = jQuery.parseJSON(data);
            if (response != false) {
                \$button.remove();
                $("tr[data-key='" + id + "'] > td").first().removeAttr("style");
            }
            else
                \$button.html('<i class="fa fa-times text-danger"></i>');
        });
    }

    return false;
} // btnSetCcProvidedOnTheFlyOnClick()

$(document).on("click", "a[id ^= 'changeOnTheFly']", btnchangeOnTheFlyOnClick);
$(document).on("click", "a[id ^= 'btnSetCcProvidedOnTheFly']", btnSetCcProvidedOnTheFlyOnClick);
JS
, yii\web\View::POS_READY);
?>