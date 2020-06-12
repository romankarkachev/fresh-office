<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\reports\TendersAnalytics */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tenders-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/reports/tenders-analytics'],
        'method' => 'get',
        'options' => ['id' => 'frmSearch'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCreatedAtStart')->widget(DateControl::class, [
                        'value' => $model->searchCreatedAtStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'начало', 'autocomplete' => 'off'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '{input}{picker}{remove}',
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
                    <?= $form->field($model, 'searchCreatedAtEnd')->widget(DateControl::class, [
                        'value' => $model->searchCreatedAtEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'конец', 'autocomplete' => 'off'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '{input}{picker}{remove}',
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
                <?= Html::submitButton('<i class="fa fa-repeat"></i> Сформировать', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', ['/reports/tenders-analytics'], ['class' => 'btn btn-default']) ?>

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
    \$button.attr("disabled", "disabled");
    text = \$button.html();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.html(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);
