<?php

use backend\controllers\PaymentOrdersController;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\components\grid\TotalAmountColumn;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PaymentOrdersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $queryString string */

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

    <div class="row">
        <div class="col-md-6">
            <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('<i class="fa fa-file-excel-o"></i> Импорт ордеров', ['import'], ['class' => 'btn btn-default']) ?>

            <?= Html::a('<i class="fa fa-close"></i> Удалить черновики', ['drop-drafts'], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить все черновые платежные ордеры из системы?',
                    'method' => 'post',
                ]
            ]) ?>

            <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', PaymentOrdersController::ROOT_URL_FOR_SORT_PAGING . '?export=true' . $queryString, ['class' => 'btn btn-default']) ?>

        </div>
    </div>
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
                    if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН && empty($model->or_at)) {
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

                    $addon = '';
                    if (!empty($model->imt_state)) {
                        $addon = '<br><span class="text-muted small" title="Статус входящего почтового отправления">' . $model->trackStateName . '</span>';
                    }

                    if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ)
                        return Html::tag('abbr', $model->{$column->attribute}, ['title' => $model->comment]) . $addon;
                    else
                        return $model->{$column->attribute} . $addon;
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
                'class' => TotalAmountColumn::class,
                'attribute' => 'amount',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\PaymentOrders */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\FinanceTransactions::getPrettyAmount($model->{$column->attribute}, 'html');
                },
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
                    if ((Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b'))) {
                        switch ($model->state_id) {
                            case PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН:
                                $buttons .= Html::a('Отметить оплаченным', '#', [
                                    'class' => 'btn btn-success btn-xs',
                                    'id' => 'changeOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН . $model->id,
                                    'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН,
                                ]);
                                break;
                            case PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН:
                                if (empty($model->ccp_at)) {
                                    $buttons .= Html::a('<i class="fa fa-file-image-o text-warning"></i> скан', '#', [
                                        'id' => 'btnSetCcProvidedOnTheFly' . $model->id,
                                        'data-id' => $model->id,
                                        'title' => 'Установить отметку наличия скана акта выполненных работ',
                                        'class' => 'btn btn-default btn-xs',
                                    ]);
                                }

                                if (empty($model->or_at)) {
                                    $buttons .= Html::a('<i class="fa fa-envelope-open-o text-warning"></i> оригинал', '#', [
                                        'id' => 'btnSetOrProvidedOnTheFly' . $model->id,
                                        'data-id' => $model->id,
                                        'title' => 'Установить отметку наличия оригинала акта выполненных работ',
                                        'class' => 'btn btn-default btn-xs',
                                    ]);
                                }

                                break;
                        }
                    }

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
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-default btn-xs']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-danger btn-xs', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '110'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<?php
$url = Url::to(['/payment-orders/change-state-on-the-fly']);
$urlCcProvided = Url::to(PaymentOrdersController::URL_SET_CCP_ON_THE_FLY_AS_ARRAY);
$urlOrProvided = Url::to(PaymentOrdersController::URL_SET_OR_ON_THE_FLY_AS_ARRAY);

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

// Обработчик щелчка по кнопкам, позволяющим отметить признак наличия сканов актов выполненных работ.
//
function btnSetCcProvidedOnTheFlyOnClick() {
    \$button = $(this);
    id = $(this).attr("data-id");
    if (id && confirm("Вы действительно хотите отметить наличие скан-копии акта выполненных работ?")) {
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

// Обработчик щелчка по кнопкам, позволяющим отметить признак наличия оригиналов актов выполненных работ.
//
function btnSetOrProvidedOnTheFlyOnClick() {
    \$button = $(this);
    id = $(this).attr("data-id");
    if (id && confirm("Вы действительно хотите отметить наличие оригинала акта выполненных работ?")) {
        $.post("$urlOrProvided?id=" + id, function(data) {
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
} // btnSetOrProvidedOnTheFlyOnClick()

$(document).on("click", "a[id ^= 'changeOnTheFly']", btnchangeOnTheFlyOnClick);
$(document).on("click", "a[id ^= 'btnSetCcProvidedOnTheFly']", btnSetCcProvidedOnTheFlyOnClick);
$(document).on("click", "a[id ^= 'btnSetOrProvidedOnTheFly']", btnSetOrProvidedOnTheFlyOnClick);
JS
, yii\web\View::POS_READY);
?>