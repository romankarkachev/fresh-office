<?php

use kartik\typeahead\Typeahead;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use yii\web\JsExpression;
use yii\widgets\Pjax;
use backend\controllers\EdfController;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\Edf */
/* @var $dataProvider yii\data\ActiveDataProvider */

$btnEmailFewPrompt = '<i class="fa fa-paper-plane text-info" aria-hidden="true"></i> файлы';
$btnDownloadFewPrompt = '<i class="fa fa-cloud-download" aria-hidden="true"></i> файлы';
$btnDeleteFewPrompt = '<i class="fa fa-trash" aria-hidden="true"></i> файлы';

$btnEmailFewId = 'emailSelectedFiles';
$btnDownloadFewId = 'downloadSelectedFiles';
$btnDeleteFewId = 'deleteSelectedFiles';

$gridViewId = 'gw-files';
?>
<div class="panel with-nav-tabs panel-success">
    <div class="panel-heading">Файлы</div>
    <div class="panel-body">
        <div class="alert alert-info" role="alert">
            <p><strong>Внимание!</strong> Файлы не связаны с формой. Загружая файлы, не сохраняйте форму! Если при загрузке файла он появился в списке, то значит он уже в базе! Просто нажимайте &laquo;Назад&raquo; в браузере, чтобы вернуться в список, или кнопку со стрелкой.</p>
        </div>
        <p>Вы можете добавить файл из хранилища. Начните вводить имя, отбор будет производиться по выбранному контрагенту. Сразу же после выбора Вами файла из предложенного списка он будет добавлен в список, а страница будет перезагружена.</p>
        <div class="row">
            <div class="col-md-3">
                <?= Typeahead::widget([
                    'name' => 'EdfFiles[fileFromStorage]',
                    'options' => [
                        'id' => 'edf-fileFromStorage',
                        'class' => 'form-control input-sm',
                        'placeholder' => 'Введите имя файла из хранилища',
                    ],
                    'scrollable' => true,
                    'pluginOptions' => ['highlight' => true],
                    'dataset' => [
                        [
                            'remote' => [
                                'url' => Url::to(EdfController::LIST_FS_TYPEAHEAD_AS_ARRAY),
                                'rateLimitWait' => 500,
                                'prepare' => new JsExpression('
function prepare(query, settings) {
    ca_id = $("#edf-fo_ca_id").val();
    settings.url += "?q=" + query + "&ca_id=" + ca_id;
    return settings;
}')
                            ],
                            'limit' => 10,
                            'display' => 'value',
                        ],
                    ],
                    'pluginEvents' => [
                        'typeahead:select' => '
function(ev, suggestion) {
    $("#edf-fileFromStorage").val(suggestion.id);
    $.post("' . Url::to(EdfController::ADD_FILE_FROM_STORAGE_AS_ARRAY) . '", {ed_id: ' . $model->id . ', file_id: suggestion.id}, function(response) {
        if (response == true) {
            $.pjax.reload({container:"#' . $gridViewId . '"});
        }
        else {
            alert("Не удалось добавить файл из хранилища!");
        }
    });
}
',
                    ],
                ]); ?>

            </div>
        </div>
        <div class="table-responsive">
            <?php Pjax::begin(['id' => 'pjax-files', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showOnEmpty' => true,
                'emptyText' => 'Файлы не прикреплялись.',
                'id' => $gridViewId,
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'options' => ['width' => '30'],
                    ],
                    [
                        'attribute' => 'ofn',
                        'label' => 'Имя файла',
                        'contentOptions' => ['style' => 'vertical-align: middle;'],
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            /** @var $model \common\models\EdfFiles */
                            /** @var $column \yii\grid\DataColumn */

                            return Html::a($model->{$column->attribute}, '#', [
                                'class' => 'link-ajax',
                                'id' => 'previewFile-' . $model->id,
                                'data-id' => $model->id,
                                'title' => 'Предварительный просмотр',
                                'data-pjax' => 0,
                            ]);
                        },
                    ],
                    [
                        'label' => 'Скачать',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            /** @var $model \common\models\EdfFiles */
                            /** @var $column \yii\grid\DataColumn */

                            return Html::a(
                                '<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>',
                                ['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/' . EdfController::DOWNLOAD_FILE_URL, 'id' => $model->id],
                                [
                                    'title' => ($model->ofn != '' ? $model->ofn . ', ' : '') . Yii::$app->formatter->asShortSize($model->size, false),
                                    'target' => '_blank',
                                    'data-pjax' => 0
                                ]
                            );
                        },
                        'options' => ['width' => '60'],
                    ],
                    [
                        'attribute' => 'uploaded_at',
                        'label' => 'Загружен',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                        'format' =>  ['date', 'dd.MM.Y HH:mm'],
                        'options' => ['width' => '130'],
                    ],
                    [
                        'attribute' => 'uploadedByProfileName',
                        'options' => ['width' => '180'],
                    ],
                    [
                        'class' => 'backend\components\grid\ActionColumn',
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => function ($url, $model) {
                                // только так не скроллится наверх (то есть при помощи заключения в форму):
                                return Html::beginForm(['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/' . EdfController::DELETE_FILE_URL, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                                    Html::a(
                                        '<i class="fa fa-trash-o"></i>',
                                        ['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/' . EdfController::DELETE_FILE_URL, 'id' => $model->id],
                                        [
                                            'title' => Yii::t('yii', 'Удалить'),
                                            'class' => 'btn btn-xs btn-danger',
                                            'aria-label' => Yii::t('yii', 'Delete'),
                                            'data-confirm' => 'Будет выполнено физическое удаление файла из данного электронного документа. Операция необратима. Продолжить?',
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                        ]
                                    ) . Html::endForm();
                            }
                        ],
                        'options' => ['width' => '20'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                    ],
                ],
            ]); ?>

            <div class="form-group">
                <?= Html::a($btnEmailFewPrompt, '#', ['id' => $btnEmailFewId, 'class' => 'btn btn-default btn-xs', 'title' => 'Отправить выделенные файлы на E-mail', 'data-pjax' => '0']) ?>

                <?= Html::a($btnDownloadFewPrompt, '#', ['id' => $btnDownloadFewId, 'class' => 'btn btn-default btn-xs', 'title' => 'Скачать выделенные файлы одним архивом', 'data-pjax' => '0']) ?>

                <?= Html::a($btnDeleteFewPrompt, '#', ['id' => $btnDeleteFewId, 'class' => 'btn btn-danger btn-xs', 'title' => 'Удалить выделенные файлы', 'data-pjax' => true]) ?>

            </div>
            <?php Pjax::end(); ?>

        </div>
    </div>
</div>
<p></p>
<div id="mw_preview" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Предпросмотр файла</h4>
            </div>
            <div id="modal_body_preview" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$modelId = $model->id;
$url = Url::to(EdfController::PREVIEW_FILE_URL_AS_ARRAY);
$urlEmailFew = Url::to(EdfController::URL_EMAIL_SELECTED_FILES_AS_ARRAY);
$urlDownloadFew = Url::to(EdfController::URL_DOWNLOAD_SELECTED_FILES_AS_ARRAY);
$urlDeleteFew = Url::to(EdfController::DELETE_FEW_FILES_URL_AS_ARRAY);

$this->registerJs(<<<JS
var checked = false;

// Выполняет пересчет количества выделенных пользователем файлов и подставляет отличное от нуля значение в текст кнопки.
//
function recountSelectedFiles() {
    var count = $("input[name ^= 'selection[]']:checked").length;
    var prompt = "";
    var promptEmail = '$btnEmailFewPrompt';
    var promptDownload = '$btnDownloadFewPrompt';
    var promptDelete = '$btnDeleteFewPrompt';
    if (count > 0) {
        prompt = " <strong>(" + count + ")</strong>";
        promptEmail += prompt;
        promptDownload += prompt;
        promptDelete += prompt;
    }

    $("#$btnEmailFewId").html(promptEmail);
    $("#$btnDownloadFewId").html(promptDownload);
    $("#$btnDeleteFewId").html(promptDelete);
} // recountSelectedFiles()

// Обработчик щелчка по ссылке "Отметить все".
//
function checkAllOnClick() {
    if (checked) {
    operation = "uncheck";
    checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= 'selection[]']").iCheck(operation);
    recountSelectedFiles();

    return false;
} // checkAllOnClick()

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id != "") {
        $("#modal_body_preview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_preview").modal();
        $("#modal_body_preview").load("$url?id=" + id);
    }

    return false;
} // previewFileOnClick()

// Обработчик щелчка по ссылке "Отправить выделенные файлы".
//
function emailSelectedFilesOnClick() {
    var ids = $("#$gridViewId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $("#btnGenerateDocs").hide();
    $("#btnFinishEdf").hide();
    $("#modalTitle").text("Отправка файлов заказчику");
    \$body = $("#modalBody");
    \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
    $("#modalWindow").modal();
    \$body.load("$urlEmailFew?id=$modelId&files=" + ids);

    return false;
} // emailSelectedFilesOnClick()

// Обработчик щелчка по ссылке "Скачать выделенные файлы".
//
function downloadSelectedFilesOnClick() {
    var ids = $("#$gridViewId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $(this).attr("href", "$urlDownloadFew?id=$modelId&ids=" + ids);
} // downloadSelectedFilesOnClick()

// Обработчик щелчка по ссылке "Удалить выделенные файлы".
//
function deleteSelectedFilesOnClick() {
    var ids = $("#$gridViewId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    if (confirm("Вы действительно хотите удалить выделенные файлы безвозвратно?")) {
        $.post("$urlDeleteFew", {ids: ids}, function() {
            $.pjax.reload({container:"#pjax-files"});
        });
    }

    return false;
} // deleteSelectedFilesOnClick()

$("input[name ^= 'selection[]']").on("ifChanged", recountSelectedFiles);
$(".select-on-check-all").on("ifClicked", checkAllOnClick);
$(document).on("click", "#$btnEmailFewId", emailSelectedFilesOnClick);
$(document).on("click", "#$btnDownloadFewId", downloadSelectedFilesOnClick);
$(document).on("click", "#$btnDeleteFewId", deleteSelectedFilesOnClick);
$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
JS
, \yii\web\View::POS_READY);
?>
