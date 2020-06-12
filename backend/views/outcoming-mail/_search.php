<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\OutcomingMailController;

/* @var $this yii\web\View */
/* @var $model common\models\OutcomingMailSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="incoming-mail-search">
    <?php $form = ActiveForm::begin([
        'action' => OutcomingMailController::URL_ROOT_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4 col-lg-4 col-lg-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchCreatedAtStart')->widget(DateControl::class, [
                                'value' => $model->searchCreatedAtStart,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => ['placeholder' => '- выберите дату -', 'autocomplete' => 'off'],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '{input}{picker}{remove}',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) {anyDateOnChange();}',
                                    ],
                                ],
                            ]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchCreatedAtEnd')->widget(DateControl::class, [
                                'value' => $model->searchCreatedAtEnd,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => ['placeholder' => '- выберите дату -', 'autocomplete' => 'off'],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '{input}{picker}{remove}',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) {anyDateOnChange();}',
                                    ],
                                ],
                            ]) ?>

                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4 col-lg-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchSentStart')->widget(DateControl::class, [
                                'value' => $model->searchSentStart,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => ['placeholder' => '- выберите дату -', 'autocomplete' => 'off'],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '{input}{picker}{remove}',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) {anyDateOnChange();}',
                                    ],
                                ],
                            ]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchSentEnd')->widget(DateControl::class, [
                                'value' => $model->searchSentEnd,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => ['placeholder' => '- выберите дату -', 'autocomplete' => 'off'],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '{input}{picker}{remove}',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) {anyDateOnChange();}',
                                    ],
                                ],
                            ]) ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'ca_src')->widget(Select2::class, [
                        'data' => \common\models\IncomingMail::arrayMapOfCaSourcesForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ])->label('Источник') ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'ca_name')->textInput(['placeholder' => 'Контрагент', 'title' => 'Поле для поиска по контрагенту, можно ввести любую часть наименования'])->label('Контрагент-получатель') ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'org_id')->widget(Select2::class, [
                        'data' => \common\models\Organizations::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ])->label('Организация-отправитель') ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'type_id')->widget(Select2::class, [
                        'data' => \common\models\IncomingMailTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', OutcomingMailController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default']) ?>

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
    text = \$button.text();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.text(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);
?>
