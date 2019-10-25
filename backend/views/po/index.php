<?php

use backend\components\TotalsColumn;
use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\controllers\PoController;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $eiAmount float сумма, в пределах которой включительно бухгалтер может согласовывать ордера без руководства */
/* @var $eiApproved array массив доступных для согласования без ведома руководства статей расходов */

$this->title = PoController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PoController::ROOT_LABEL;

if (!isset($eiApproved)) $eiApproved = false; // если массив не передается, то процедура согласования и не нужна
?>
<div class="po-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => function ($model, $key, $index, $grid) {
            /* @var $model \common\models\Po */

            $options = [];
            switch ($model->state_id) {
                case PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ:
                    $options = ['class' => 'danger'];
                    break;
                case PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН:
                    $options = ['class' => 'success'];
                    break;
            }

            return $options;
        },
        'showFooter' => true,
        'footerRowOptions' => ['class' => 'text-right'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
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
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ)
                        return Html::tag('abbr', $model->{$column->attribute}, ['title' => $model->comment]);
                    else
                        return $model->{$column->attribute};
                },
            ],
            'companyName',
            [
                'attribute' => 'eiRepHtml',
                'format' => 'raw',
                'footer' => '<strong>Итого:</strong>',
                'footerOptions' => ['class' => 'text-right'],
            ],
            [
                'class' => TotalsColumn::className(),
                'attribute' => 'amount',
                'format' => 'currency',
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'paid_at',
                'label' => 'Оплачено',
                'format' => ['datetime', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'label' => 'Быстрая реакция',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($eiAmount, $eiApproved) {
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    $buttons = '';

                    // кнопки логиста
                    if (Yii::$app->user->can('logist') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК)
                        $buttons = Html::a('На согласование', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'setPaidOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ . $model->id,
                            'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ,
                        ]);

                    // кнопки руководителя
                    if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')) && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ) {
                        // согласование доступно для пользователей с полными правами, а также бухгалтеров по бюджету в
                        // рамках заданных им в их профилях сумм и статей расходов
                        if ((false !== $eiApproved && in_array($model->ei_id, $eiApproved) && $model->amount <= $eiAmount) || Yii::$app->user->can('root')) {
                            $buttons = Html::a('Согласовать', '#', [
                                'class' => 'btn btn-success btn-xs',
                                'id' => 'setPaidOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН . $model->id,
                                'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН,
                            ]);
                        }
                    }

                    // кнопки бухгалтера
                    if ((Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН)
                        $buttons = Html::a('Отметить оплаченным', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'setPaidOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН . $model->id,
                            'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН,
                        ]);

                    return '<div id="block-state' . $model->id . '">' . $buttons . '</div>';
                },
                'options' => ['width' => '200'],
            ],
            [
                'attribute' => 'comment',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    return nl2br($model->{$column->attribute});
                },
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
            ],
        ],
    ]); ?>

</div>
<?php
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
    }, 1000);
}
JS
, View::POS_BEGIN);

$urlSetPaidOnTheFly = Url::to(PoController::URL_SET_PAID_ON_THE_FLY_AS_ARRAY);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопкам модерации.
//
function btnSetPaidOnTheFlyOnClick() {
    id = $(this).attr("data-id");
    state = $(this).attr("data-state");
    if (id != undefined && id != "" && state != undefined && state != "") {
        \$block = $("#block-state" + id).html("<i class=\"fa fa-cog fa-spin text-muted\"></i>");
        $.post("$urlSetPaidOnTheFly?po_id=" + id + "&state_id=" + state, function(data) {
            if (data == true)
                \$block.html('<i class="fa fa-check-circle-o text-success"></i>');
            else
                \$block.html('<i class="fa fa-times text-danger"></i>');
        });
    }

    return false;
} // btnSetPaidOnTheFlyOnClick()

$(document).on("click", "a[id ^= 'setPaidOnTheFly']", btnSetPaidOnTheFlyOnClick);
JS
, View::POS_READY);
?>
