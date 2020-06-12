<?php

use yii\helpers\Html;
use yii\web\View;
use backend\components\grid\GridView;
use backend\components\grid\TotalAmountColumn;
use backend\controllers\AdvanceReportsController;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $renderRestricted bool признак, определяющий расширенное или ограниченное использование инструмента */

$this->title = AdvanceReportsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = AdvanceReportsController::ROOT_LABEL;
?>
<div class="po-list">
    <?= $this->render('_search', ['model' => $searchModel, 'renderRestricted' => $renderRestricted]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Новый', AdvanceReportsController::URL_NEW_AS_ARRAY, ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => function ($model, $key, $index, $grid) {
            /* @var $model \common\models\Po */

            $options = [];
            switch ($model->state_id) {
                case \common\models\PaymentOrdersStates::PAYMENT_STATE_ОТКЛОНЕННЫЙ_АВАНСОВЫЙ_ОТЧЕТ:
                    $options = ['class' => 'danger'];
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
            [
                'attribute' => 'createdByProfileName',
                'visible' => !$renderRestricted,
            ],
            'companyName',
            [
                'attribute' => 'eiRepHtml',
                'format' => 'raw',
                'footer' => '<strong>Итого:</strong>',
                'footerOptions' => ['class' => 'text-right'],
            ],
            [
                'class' => TotalAmountColumn::class,
                'attribute' => 'amount',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\FinanceTransactions::getPrettyAmount($model->{$column->attribute}, 'html');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
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
                'label' => 'Быстро',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    $buttons = '';

                    if (Yii::$app->user->can('root') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ) {
                        // согласование доступно для пользователей с полными правами
                        $buttons = Html::a('Согласовать', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'setPaidOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН . $model->id,
                            'data-id' => $model->id, 'data-state' => PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН,
                        ]);
                    }

                    /*
                    if (Yii::$app->user->can('accountant_b') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ)
                        $buttons = Html::a('Оплачено', '#', [
                            'class' => 'btn btn-success btn-xs',
                            'id' => 'setPaidOnTheFly' . PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН . $model->id,
                            'data-id' => $model->id,
                            'data-state' => PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН,
                            'title' => 'Отметить этот авансовый отчет оплаченным текущей датой',
                        ]);
                    */

                    return '<div id="block-state' . $model->id . '">' . $buttons . '</div>';
                },
                'options' => ['width' => '100'],
                'visible' => !$renderRestricted,
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{view} {moderate} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="fa fa-ellipsis-h"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'moderate' => function ($url, $model) {
                        return Html::a('<i class="fa fa-ellipsis-h"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    },
                ],
                'visibleButtons' => [
                    'view' => $renderRestricted,
                    'moderate' => !$renderRestricted,
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

$urlSetPaidOnTheFly = \yii\helpers\Url::to(AdvanceReportsController::URL_SET_PAID_ON_THE_FLY_AS_ARRAY);

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
