<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */

$this->title = 'Производство | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Производство';

$model = new \common\models\ProductionFeedbackForm();
$formName = $model->formName();
$formNameForId = strtolower($model->formName());
unset($model);
?>
<div class="production-feedback-form">
    <div class="row">
        <div class="col-md-2">
            <div class="form-group field-<?= $formNameForId ?>-project_id">
                <label class="control-label" for="<?= $formNameForId ?>-project_id">ID проекта</label>
                <div class="input-group">
                    <?= Html::input('text', 'ProductionFeedbackForm[project_id]', '', [
                        'id' => $formNameForId . '-project_id',
                        'class' => 'form-control',
                        'placeholder' => 'Введите ID проекта',
                    ]) ?>

                    <span class="input-group-btn"><button class="btn btn-default" type="button" id="btnFetchProjectData"><i class="fa fa-search" aria-hidden="true"></i> Найти</button></span></div>
                <p class="help-block help-block-error"></p>
            </div>
        </div>
    </div>
    <div id="block-project_data"></div>
</div>
<?php
$formName_documents_match = $formName . '[documents_match]';

$promptIsMatch = 'Вес и количество соответствуют АПП.';
$promptIsMismatch = 'Внимание! Груз не соответствует документам. Ниже подробное описание несоответствия.';

$url_project_data = Url::to(['/production/fetch-project-data']);
$url_process = Url::to(['/production/process-project']);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Найти проект".
//
function btnFetchProjectDataOnClick() {
    project_id = $("#$formNameForId-project_id").val();
    if (project_id != "" && project_id != undefined) {
        $("#block-project_data").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#block-project_data").load("$url_project_data?project_id=" + project_id);
    }

    return false;
} // btnFetchProjectDataOnClick()

// Обработчик щелчка по группе радиокнопок.
//
function documentsMatchOnClick() {
    value = $("input[name='$formName_documents_match']:checked").val();
    value++;
    $("#$formNameForId-action").val(value);
    $("#block-documents_match").hide();
    $("#block-project_details").hide();
    switch (value) {
        case 1:
            // груз документам не соответствует
            $("#$formNameForId-message_body").val($("#$formNameForId-message_body").val() + " $promptIsMismatch");
            $("#block-invoice_mismatch").show();
            break;
        case 2:
            // груз соответствует документам
            $("#$formNameForId-message_body").val($("#$formNameForId-message_body").val() + " $promptIsMatch");
            break;
    }
    $("#block-feedback").show();

    return false;
} // documentsMatchOnClick()

$("#$formNameForId-project_id").on("keypress", function (e) {
     if (e.which === 13) {
        btnFetchProjectDataOnClick();
     }
});

$(document).on("click", "#btnFetchProjectData", btnFetchProjectDataOnClick);
$(document).on("change", "#$formNameForId-documents_match", documentsMatchOnClick);
JS
, \yii\web\View::POS_READY);
?>
