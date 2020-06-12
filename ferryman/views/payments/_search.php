<?php

use yii\helpers\Html;
use kartik\date\DatePicker;
use common\components\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use ferryman\controllers\PaymentsController;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrdersSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="payment-orders-search">
    <?php $form = ActiveForm::begin([
        'action' => PaymentsController::URL_ROOT,
        'method' => 'get',
    ]); ?>

    <div class="card">
        <div class="card-header card-header-info card-header-inverse"><i class="fa fa-filter"></i> Форма отбора</div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPaymentDateStart')->widget(DateControl::class, [
                        'value' => $model->searchPaymentDateStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => '- дата оплаты -', 'title' => 'Выберите начало периода для отбора по дате оплаты', 'autocomplete' => 'off'],
                            'type' => DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
                            'pickerButton' => '<span class="input-group-addon kv-date-calendar" title="Выбрать дату"><i class="fa fa-calendar" aria-hidden="true"></i></span>',
                            // можно и добавить, но верстка ломается:
                            //'removeButton' => '<span class="input-group-addon kv-date-remove" title="Очистить поле"><i class="fa fa-remove" aria-hidden="true"></i></span>',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) { anyDateOnChange(); }',
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPaymentDateEnd')->widget(DateControl::class, [
                        'value' => $model->searchPaymentDateEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => '- дата оплаты -', 'title' => 'Выберите конец периода для отбора по дате оплаты', 'autocomplete' => 'off'],
                            'type' => DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
                            'pickerButton' => '<span class="input-group-addon kv-date-calendar" title="Выбрать дату"><i class="fa fa-calendar" aria-hidden="true"></i></span>',
                            // можно и добавить, но верстка ломается:
                            //'removeButton' => '<span class="input-group-addon kv-date-remove" title="Очистить поле"><i class="fa fa-remove" aria-hidden="true"></i></span>',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) { anyDateOnChange(); }',
                            ],
                        ],
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-filter"></i> Выполнить отбор', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', PaymentsController::URL_ROOT, ['class' => 'btn btn-secondary']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    \$button = $("#btnSearch");
    if (!\$button.is(":disabled")) {
        \$button.attr("disabled", "disabled");
        text = \$button.html();
        \$button.text("Подождите...");
        setTimeout(function () {
            \$button.removeAttr("disabled");
            \$button.html(text);
        }, 1500);
    }
}
JS
, \yii\web\View::POS_BEGIN);
?>
