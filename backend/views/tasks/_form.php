<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use common\models\User;
use common\models\TasksTypes;
use common\models\TasksStates;
use common\models\TasksPriorities;
use common\models\TransportRequests;
use backend\controllers\TasksController;

/* @var $this yii\web\View */
/* @var $model common\models\Tasks */
/* @var $form yii\bootstrap\ActiveForm */

$formNameId = strtolower($model->formName());
$urlFetchContactPersons = Url::to(['/correspondence-packages/fetch-contact-persons']);
?>

<div class="tasks-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'type_id')->widget(Select2::class, [
                'data' => TasksTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::class, [
                'data' => TasksStates::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'priority_id')->widget(Select2::class, [
                'data' => TasksPriorities::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'responsible_id')->widget(Select2::class, [
                'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'project_id')->widget(MaskedInput::class, [
                'mask' => '99999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'ID проекта']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'fo_ca_id')->widget(Select2::class, [
                'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(['correspondence-packages/counteragent-casting-by-name']),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) {
if (!result.custom) return result.text;
$("#' . $formNameId . '-fo_ca_name").val(result.text);
$contactField = $("#' . $formNameId . '-fo_cp_id");
$.get("' . $urlFetchContactPersons . '?id=" + result.id, function (response) {
    $contactField.empty().trigger("change");
    $.each(response, function(index, value) {
        var newOption = new Option(value.text, value.id, true, true);
        $contactField.append(newOption).trigger("change");
    });
});
return result.text;
}'),
                ],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fo_cp_id')->widget(Select2::class, [
                'initValueText' => $model->fo_cp_name != null ? $model->fo_cp_name : '',
                'data' => !empty($model->fo_cp_id) ? $model->arrayMapOfContactPersonsForSelect2() : [],
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'start_at')->widget(DateControl::class, [
                'value' => $model->start_at,
                'type' => DateControl::FORMAT_DATETIME,
                'displayFormat' => 'php:d.m.Y H:i',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => '- выберите дату и время -',
                        'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'finish_at')->widget(DateControl::class, [
                'value' => $model->finish_at,
                'type' => DateControl::FORMAT_DATETIME,
                'displayFormat' => 'php:d.m.Y H:i',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => '- выберите дату и время -',
                        'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'purpose')->textarea(['rows' => 3, 'placeholder' => 'Введите цель задачи']) ?>

    <?= $form->field($model, 'solution')->textarea(['rows' => 3, 'placeholder' => 'Введите результат']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . TasksController::ROOT_LABEL, TasksController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?= $form->field($model, 'fo_ca_name')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'fo_cp_name')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Контактное лицо контрагента".
//
function contactPersonOnChange() {
    var data = $(this).select2("data");
    if (data.length > 0) {
        $("#$formNameId-fo_cp_name").val(data[0].text);
    }
} // contactPersonOnChange()

$(document).on("change", "#$formNameId-fo_cp_id", contactPersonOnChange);
JS
, \yii\web\View::POS_READY);
?>
