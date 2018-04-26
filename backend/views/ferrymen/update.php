<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpFiles \yii\data\ActiveDataProvider */
/* @var $dpBankDetails common\models\FerrymenBankDetails[] */
/* @var $dpBankCards common\models\FerrymenBankCards[] */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Перевозчики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="ferrymen-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <?php if (!$model->isNewRecord): ?>
    <div class="row">
        <div class="col-md-7">
            <?= $this->render('_bank_details', [
                'model' => $model,
                'dpBankDetails' => $dpBankDetails
            ]) ?>

        </div>
        <div class="col-md-5">
            <?= $this->render('_bank_cards', [
                'model' => $model,
                'dpBankCards' => $dpBankCards
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <?= $this->render('_drivers', [
                'model' => $model,
                'dpDrivers' => $dpDrivers
            ]) ?>

        </div>
        <div class="col-md-7">
            <?= $this->render('_transport', [
                'model' => $model,
                'dpTransport' => $dpTransport
            ]) ?>

        </div>
    </div>
    <div class="page-header"><h3>Файлы</h3></div>
    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?php if (Yii::$app->user->can('root')): ?>
    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/ferrymen/upload-files']),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

    <?php endif; ?>
    <div id="mw_preview" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-info" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal_title" class="modal-title">Modal title</h4>
                </div>
                <div id="modalBodyPreview" class="modal-body">
                    <p>One fine body…</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
$url = Url::to(['/ferrymen/preview-file']);

$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id != "") {
        $("#modal_title").text("Предпросмотр изображения");
        $("#modalBodyPreview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_preview").modal();
        $("#modalBodyPreview").load("$url?id=" + id);
    }

    return false;
} // previewFileOnClick()

$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
JS
, \yii\web\View::POS_READY);
?>
