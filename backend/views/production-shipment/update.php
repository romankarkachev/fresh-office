<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\ProductionShipment;
use common\models\ProductionShipmentFiles;

/* @var $this yii\web\View */
/* @var $model common\models\ProductionShipment */
/* @var $dpFiles \yii\data\ActiveDataProvider of common\models\ProductionShipmentFiles */

$transportRep = $model->transportRep . ' (' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y H:i') . ')';

$this->title = $transportRep . HtmlPurifier::process(' &mdash; ' . ProductionShipment::LABEL_ROOT . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => ProductionShipment::LABEL_ROOT, 'url' => ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY];
$this->params['breadcrumbs'][] = $transportRep;

$fileInputId = ProductionShipmentFiles::DOM_IDS['FILE_INPUT_ID'];
$pjaxId = ProductionShipmentFiles::DOM_IDS['PJAX_ID'];
?>
<div class="production-shipment-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files_list', ['dataProvider' => $dpFiles]); ?>

    <?= \kartik\file\FileInput::widget([
        'id' => $fileInputId,
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(ProductionShipment::URL_UPLOAD_FILES_AS_ARRAY),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modal_title" class="modal-title">Предпросмотр файла</h4></div>
            <div id="mwBody" class="modal-body"></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button></div>
        </div>
    </div>
</div>

<?php
$urlPreview = Url::to(ProductionShipment::URL_PREVIEW_FILE);
$urlReload = Url::to([ProductionShipment::URL_RENDER_FILES, 'id' => $model->id]);

$this->registerJs(<<<JS

$("#$fileInputId").on("fileuploaded filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({type:"post", container:"#$pjaxId", url:"$urlReload", replace:false, push:false});
});

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id) {
        \$body = $("#mwBody");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-lg text-muted"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        \$body.load("$urlPreview?id=" + id);
    }

    return false;
} // previewFileOnClick()

$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
JS
, \yii\web\View::POS_READY);
?>
