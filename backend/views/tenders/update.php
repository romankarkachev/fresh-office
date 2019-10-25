<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use \backend\controllers\TendersController;
use common\models\TendersFiles;

/* @var $this yii\web\View */
/* @var $model common\models\Tenders */
/* @var $newWasteModel common\models\TendersTp */
/* @var $searchFilesModel common\models\TendersFilesSearch */
/* @var $searchLogsModel common\models\TendersLogsSearch */
/* @var $dpWaste \yii\data\ActiveDataProvider of common\models\TendersTp */
/* @var $dpFiles \yii\data\ActiveDataProvider of common\models\TendersFiles */
/* @var $dpLogs \yii\data\ActiveDataProvider of common\models\TendersLogs */

$modelId = $model->id;
$modelRep = 'Закупка № ' . $model->oos_number;

// представление тендера дополним информацией о нем
$tenderProperties = [];
if (!empty($model->revision)) {
    // добавляем к описанию номер редакции
    $tenderProperties[] = 'ред. ' . $model->revision;
}

if (!empty($model->law_no)) {
    // добавляем к описанию номер закона
    $tenderProperties[] = $model->lawName;
}

if (count($tenderProperties) > 0) {
    $modelRep .= ' (' . trim(implode(', ', $tenderProperties), ', ') . ')';
}

$this->title = 'Тендер ID ' . $model->id . ', закупка ' . $model->oos_number . HtmlPurifier::process(' &mdash; ' . TendersController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $modelRep;

$wasteCount = $dpWaste->getTotalCount();
$filesCount = $dpFiles->getTotalCount();
$logsCount = $dpLogs->getTotalCount();
?>
<div class="tenders-update">
    <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
        <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
        <li role="presentation"><a href="#waste" aria-controls="waste" role="tab" data-toggle="tab">Отходы<?= empty($wasteCount) ? '' : ' (' . $wasteCount . ')' ?></a></li>
        <li role="presentation"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Файлы<?= empty($filesCount) ? '' : ' (' . $filesCount . ')' ?></a></li>
        <li role="presentation"><a href="#logs" aria-controls="logs" role="tab" data-toggle="tab">История<?= empty($logsCount) ? '' : ' (' . $logsCount . ')' ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="common">
            <?= $this->render('_form', ['model' => $model]) ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="waste">
            <?= $this->render('_waste_list', ['model' => $newWasteModel, 'dataProvider' => $dpWaste]); ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="files">
            <?= $this->render('_files_list', ['tender' => $model, 'searchModel' => $searchFilesModel, 'dataProvider' => $dpFiles]); ?>

            <?php if ($model->state_id > \common\models\TendersStates::STATE_ДОЗАПРОС): ?>
            <?= \kartik\file\FileInput::widget([
                'id' => 'new_files',
                'name' => 'files[]',
                'options' => ['multiple' => true],
                'pluginOptions' => [
                    'maxFileCount' => 10,
                    'uploadAsync' => false,
                    'uploadUrl' => \yii\helpers\Url::to(TendersController::URL_UPLOAD_FILES_AS_ARRAY),
                    'uploadExtraData' => [
                        'obj_id' => $model->id,
                    ],
                ]
            ]) ?>

            <?php endif; ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="logs">
            <?= $this->render('_logs_list', ['searchModel' => $searchLogsModel, 'dataProvider' => $dpLogs]); ?>

        </div>
    </div>
</div>
<?php
$urlPreview = Url::to(TendersController::URL_PREVIEW_FILE_AS_ARRAY);
$urlDownloadSelected = Url::to(TendersController::URL_DOWNLOAD_SELECTED_FILES_AS_ARRAY);

$btnDownloadFewPrompt = TendersFiles::DOM_IDS['BUTTON_DOWNLOAD_SELECTED_PROMPT'];
$gwFilesId = TendersFiles::DOM_IDS['GRIDVIEW_ID'];

$this->registerJs(<<<JS
// Выполняет инициализацию галочек (необходимо например в случае интерактивной перезагрузки страницы).
//
function initializeCheckboxes() {
    $("input[type='checkbox']").iCheck({checkboxClass: "icheckbox_square-green"});
} // initializeCheckboxes()
JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS

var checkedFiles = false;

$("#new_files").on("fileuploaded filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#pjax-files"});
});

// Выполняет пересчет количества выделенных пользователем файлов и подставляет отличное от нуля значение в текст кнопки.
//
function recountSelectedFiles() {
    var count = $("input[name ^= 'selection[]']:checked").length;
    var prompt = "$btnDownloadFewPrompt";
    if (count > 0) {
        prompt += " <strong>(" + count + ")</strong>";
    }

    $("#downloadSelectedFiles").html(prompt);
} // recountSelectedFiles()

// Обработчик щелчка по ссылке "Отметить все".
//
function checkAllOnClick() {
    if (checkedFiles) {
    operation = "uncheck";
    checkedFiles = false;
    }
    else {
        operation = "check";
        checkedFiles = true;
    }

    $("input[name ^= 'selection[]']").iCheck(operation);
    recountSelectedFiles();

    return false;
} // checkAllOnClick()

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id) {
        $("#modal_body_preview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_preview").modal();
        $("#modal_body_preview").load("$urlPreview?id=" + id);
    }

    return false;
} // previewFileOnClick()

// Обработчик щелчка по ссылке "Скачать выделенные файлы".
//
function downloadSelectedFilesOnClick() {
    var ids = $("#$gwFilesId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $(this).attr("href", "$urlDownloadSelected?id=$modelId&ids=" + ids);
} // downloadSelectedFilesOnClick()

$(document).on("pjax:success", "#pjax-files", initializeCheckboxes);
$(document).on("change ifChanged", "input[name ^= 'selection[]']", recountSelectedFiles);
$(document).on("click ifClicked", ".select-on-check-all", checkAllOnClick);
$(document).on("click", "#downloadSelectedFiles", downloadSelectedFilesOnClick);
$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);

initializeCheckboxes();
JS
);
?>
