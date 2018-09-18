<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\foProjectsSearch */
/* @var $form yii\bootstrap\ActiveForm */

$template = '<div><h5><strong>{{value}}</strong></h5>' . '<em class="text-muted">Водители: {{drivers}}</em><br/><em class="text-muted">Транспорт: {{transport}}</em></div>';
?>

<div class="ferrymen-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/ferrymen/missing-drivers-transport'],
        'method' => 'get',
        'options' => ['id' => 'frm-search'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchVivozDateFrom')->widget(DateControl::className(), [
                        'value' => $model->searchVivozDateFrom,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'Начало периода', 'title' => 'Дата вывоза'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
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
                                'changeDate' => 'function(e) {
anyDateOnChange();
                                    }',
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchVivozDateTo')->widget(DateControl::className(), [
                        'value' => $model->searchVivozDateTo,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'Конец периода', 'title' => 'Дата вывоза'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
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
                                'changeDate' => 'function(e) {
anyDateOnChange();
                                    }',
                            ],
                        ],
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', ['/ferrymen/missing-drivers-transport'], ['class' => 'btn btn-default']) ?>

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
