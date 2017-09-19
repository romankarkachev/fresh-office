<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Производство | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Производство';

//production-closing-projects
?>
<div class="production-closing-projects">
    <div class="production-closing-projects-form">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group field-productionclosingprojects-project_id">
                    <label class="control-label" for="productionclosingprojects-project_id">ID проекта</label>
                    <div class="input-group">
                        <?= Html::input('text', 'ProductionClosingProjects[project_id]', '', [
                            'id' => 'productionclosingprojects-project_id',
                            'class' => 'form-control',
                            'placeholder' => 'Введите ID проекта',
                        ]) ?>

                        <span class="input-group-btn"><button class="btn btn-default" type="button" id="btnFetchProjectData"><i class="fa fa-search" aria-hidden="true"></i> Найти</button></span></div>
                    <p class="help-block help-block-error"></p>
                </div>
            </div>
        </div>
        <div id="block-documents_match" class="form-group field-productionclosingprojects-documents_match">
            <div id="block-project_data"></div>
        </div>
    </div>
</div>
<?php
$url_project_data = Url::to(['/production/fetch-project-data']);
$url_process = Url::to(['/production/process-project']);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Найти проект".
//
function btnFetchProjectDataOnClick() {
    project_id = $("#productionclosingprojects-project_id").val();
    if (project_id != "" && project_id != undefined) {
        $("#block-project_data").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#block-project_data").load("$url_project_data?project_id=" + project_id);
    }
} // btnFetchProjectDataOnClick()

// Обработчик щелчка по группе радиокнопок.
//
function documentsMatchOnClick() {
    value = $("input[name='ProductionClosingProjects[documents_match]']:checked").val();
    value++;
    project_id = $("#productionclosingprojects-project_id").val();
    if (project_id != "" && project_id != undefined) {
        $.post("$url_process", {project_id: project_id, action: value}, function (result) {
            if (result == true) {
                $("#block-project_data").html('<div class="alert alert-success">Статус проекта успешно изменен.</div');
            }
            else
                $("#block-project_data").html('<div class="alert alert-danger">Не удалось применить требуемые статусы проекта.</div');
        });
    }
} // documentsMatchOnClick()

$("#productionclosingprojects-project_id").on("keypress", function (e) {
     if (e.which === 13) {
        btnFetchProjectDataOnClick();
     }
});

$(document).on("click", "#btnFetchProjectData", btnFetchProjectDataOnClick);
$(document).on("change", "#productionclosingprojects-documents_match", documentsMatchOnClick);
JS
, \yii\web\View::POS_READY);
?>
