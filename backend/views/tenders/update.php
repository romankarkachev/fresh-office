<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;
use common\models\AuthItem;
use common\models\TendersFiles;
use common\models\TendersResults;
use common\models\TenderParticipantForms;
use backend\controllers\TendersController;

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
$winner = $model->winner;
if (empty($winner)) {
    $winner = new \common\models\TendersResults([
        'tender_id' => $modelId,
    ]);
}
?>
<div class="tenders-update">
    <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
        <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
        <li role="presentation"><a href="#waste" aria-controls="waste" role="tab" data-toggle="tab">Отходы<?= empty($wasteCount) ? '' : ' (' . $wasteCount . ')' ?></a></li>
        <li role="presentation"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Файлы<?= empty($filesCount) ? '' : ' (' . $filesCount . ')' ?></a></li>
        <li role="presentation"><a href="#logs" aria-controls="logs" role="tab" data-toggle="tab">История<?= empty($logsCount) ? '' : ' (' . $logsCount . ')' ?></a></li>
        <li role="presentation"><a href="#winner" aria-controls="winner" role="tab" data-toggle="tab">Победитель<?= empty($winner->id) ? '' : ' (1)' ?></a></li>
        <?php if (Yii::$app->user->can(AuthItem::ROLE_ROOT)): ?>
        <li role="presentation"><a href="#forms" aria-controls="forms" role="tab" data-toggle="tab">Формы</a></li>
        <?php endif; ?>
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

        </div>
        <div role="tabpanel" class="tab-pane" id="logs">
            <?= $this->render('_logs_list', ['searchModel' => $searchLogsModel, 'dataProvider' => $dpLogs]); ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="winner">
            <?= $this->render('_winner', ['model' => $winner]); ?>

        </div>
        <?php if (Yii::$app->user->can(AuthItem::ROLE_ROOT)): ?>
        <div role="tabpanel" class="tab-pane" id="forms">
            <?= $this->render('_forms', ['model' => new TenderParticipantForms(['tender_id' => $modelId])]); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$dadataToken = common\models\DadataAPI::API_TOKEN;
$urlPreview = Url::to(TendersController::URL_PREVIEW_FILE_AS_ARRAY);
$urlDownloadSelected = Url::to(TendersController::URL_DOWNLOAD_SELECTED_FILES_AS_ARRAY);

$btnDownloadFewPrompt = TendersFiles::DOM_IDS['BUTTON_DOWNLOAD_SELECTED_PROMPT'];
$gwFilesId = TendersFiles::DOM_IDS['GRIDVIEW_ID'];

$btnSubmitTenderResults = TendersResults::DOM_IDS['BUTTON_ID'];
$frmTenderResults = TendersResults::DOM_IDS['FORM_ID'];

// инструменты для работы виджета dadata
$this->registerCssFile('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/css/suggestions.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/js/jquery.suggestions.min.js', ['depends' => 'yii\web\JqueryAsset', 'position' => View::POS_END]);
$trNameId = Html::getInputId($winner, 'name');
$trInnId = Html::getInputId($winner, 'inn');
$trKppId = Html::getInputId($winner, 'kpp');
$trOgrnId = Html::getInputId($winner, 'ogrn');

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

// Обработчик нажатия на кнопку "Сохранить" в форме интерактивного ввода победителя в торгах.
//
function btnSubmitTenderResultsOnClick() {
    \$form = $("#$frmTenderResults");
    \$btn = $(this);
    \$btn.button("loading");

    $.post(\$form.attr("action"), \$form.serialize(), function (response) {
        if (response !== false) {
            $("#winner").html(response);
        }
        else {
            \$form.before('<p class="text-danger">Не удалось сохранить информацию о победителе.</p>');
        }
    }).always(function () {
        \$btn.button("reset");
    });

    return false;
} // btnSubmitTenderResultsOnClick()

$("#dadataCasting").suggestions({
    token: "$dadataToken",
    type: "PARTY",
    onSelect: function(suggestion) {
        $("#$trNameId").val("");
        $("#$trInnId").val("");
        $("#$trKppId").val("");
        $("#$trOgrnId").val("");

        if (suggestion.data.state.liquidation_date != null)  {
            if (!confirm("Предприятие ликвидировано! Вы действительно хотите продолжить?")) {
                return true;
            }
        }

        response = suggestion.data;

        if (response.name.full) $("#$trNameId").val(response.name.full);
        if (response.inn) $("#$trInnId").val(response.inn);
        if (response.kpp) $("#$trKppId").val(response.kpp);
        if (response.ogrn) $("#$trOgrnId").val(response.ogrn);

        return true;
    }
});

$(document).on("pjax:success", "#pjax-files", initializeCheckboxes);
$(document).on("change ifChanged", "input[name ^= 'selection[]']", recountSelectedFiles);
$(document).on("click ifClicked", ".select-on-check-all", checkAllOnClick);
$(document).on("click", "#downloadSelectedFiles", downloadSelectedFilesOnClick);
$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
$(document).on("click", "#$btnSubmitTenderResults", btnSubmitTenderResultsOnClick);

initializeCheckboxes();
JS
, View::POS_READY);
?>
