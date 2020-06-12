<?php

use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\components\grid\TotalAmountColumn;
use backend\controllers\PoController;
use common\models\AuthItem;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $eiAmount float сумма, в пределах которой включительно бухгалтер может согласовывать ордера без руководства */
/* @var $eiApproved array массив доступных для согласования без ведома руководства статей расходов */
/* @var $queryString string */
/* @var $roleName string */

$this->title = PoController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PoController::ROOT_LABEL;

if (!isset($eiApproved)) $eiApproved = false; // если массив не передается, то процедура согласования и не нужна

$btnDelete = Html::a('<i class="fa fa-trash-o"></i>', '%URL_DELETE%', ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post']);
if (!Yii::$app->user->can(AuthItem::ROLE_ROOT)) {
    $btnDelete = Html::a('<i class="fa fa-trash text-danger"></i>', '%URL_DELETE%', ['title' => 'Установить пометку удаления', 'class' => 'btn btn-xs btn-default', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post']);
}
?>
<div class="po-list">
    <?= $this->render('_search', ['model' => $searchModel, 'roleName' => $roleName]); ?>

    <div class="row form-group">
        <div class="col-md-6">
            <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('<i class="fa fa-file-excel-o"></i> Импорт зарплаты', PoController::URL_IMPORT_SALARY_AS_ARRAY, ['class' => 'btn btn-default', 'title' => 'Импорт платежных ордеров по зарплате']) ?>

            <?= Html::a('<i class="fa fa-file-excel-o"></i> Импорт налогов', PoController::URL_IMPORT_WAGE_FUND_AS_ARRAY, ['class' => 'btn btn-default', 'title' => 'Импорт платежных ордеров по отчислениям с ФОТ']) ?>

            <?php if ($roleName != AuthItem::ROLE_ACCOUNTANT_SALARY): ?>
            <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', PoController::ROOT_URL_FOR_SORT_PAGING . '?export=true' . $queryString, ['class' => 'btn btn-default']) ?>

            <?php endif; ?>
        </div>
    </div>
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
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    $deletedAddon = '';
                    if ($model->is_deleted) {
                        $deletedAddon = '<br/><i class="fa fa-times btn-block text-danger" aria-hidden="true" title="Элемент помечен на удаление"></i>';
                    }

                    $arAddon = '';
                    if (!empty($model->isAdvancedReportInThePast)) {
                        $arAddon = '<br/><span class="text-cende text-muted small">авансовый</span>';
                    }

                    return Yii::$app->formatter->asDate($model->{$column->attribute}, 'php:d.m.Y H:i') . $arAddon . $deletedAddon;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'createdByProfileName:ntext:Автор',
            [
                'attribute' => 'stateName',
                'label' => 'Статус',
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
                'buttons' => [
                    'delete' => function ($url, $model) use ($btnDelete) {
                        return str_replace('%URL_DELETE%', $url, $btnDelete);
                    },
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
