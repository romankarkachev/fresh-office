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
/* @var $dpPaymentOrders common\models\PaymentOrders[] */
/* @var $poTotalAmount float общая сумма по всем платежным ордерам (вне зависимости от номера просматриваемой страницы) */
/* @var $dpOrders common\models\foProjects[] */
/* @var $ordersTotalAmount float общая сумма по всем рейсам (вне зависимости от номера просматриваемой страницы) */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Перевозчики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = $model->name;

$baCount = $dpBankDetails->getTotalCount();
$bcCount = $dpBankCards->getTotalCount();
$driversCount = $dpDrivers->getTotalCount();
$transportCount = $dpTransport->getTotalCount();
$poCount = $dpPaymentOrders->getTotalCount();
$freightsCount = $dpOrders->getTotalCount();
$filesCount = $dpFiles->getTotalCount();
?>
<div class="ferrymen-update">
    <?php if (!$model->isNewRecord): ?>
    <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
        <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
        <li role="presentation"><a href="#ba" aria-controls="ba" role="tab" data-toggle="tab">Банковские счета<?= empty($baCount) ? '' : ' (' . $baCount . ')' ?></a></li>
        <li role="presentation"><a href="#bc" aria-controls="bc" role="tab" data-toggle="tab">Банковские карты<?= empty($bcCount) ? '' : ' (' . $bcCount . ')' ?></a></li>
        <li role="presentation"><a href="#drivers" aria-controls="drivers" role="tab" data-toggle="tab">Водители<?= empty($driversCount) ? '' : ' (' . $driversCount . ')' ?></a></li>
        <li role="presentation"><a href="#transport" aria-controls="transport" role="tab" data-toggle="tab">Транспорт<?= empty($transportCount) ? '' : ' (' . $transportCount . ')' ?></a></li>
        <li role="presentation"><a href="#payment_orders" aria-controls="payment_orders" role="tab" data-toggle="tab">Платежные ордеры<?= empty($poCount) ? '' : ' (' . $poCount . ')' ?></a></li>
        <li role="presentation"><a href="#freights" aria-controls="freights" role="tab" data-toggle="tab">Рейсы<?= empty($freightsCount) ? '' : ' (' . $freightsCount . ')' ?></a></li>
        <li role="presentation"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Файлы<?= empty($filesCount) ? '' : ' (' . $filesCount . ')' ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="common">
            <?php endif; ?>
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>

        <?php if (!$model->isNewRecord): ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="ba">
            <?= $this->render('_bank_details', [
                'model' => $model,
                'dpBankDetails' => $dpBankDetails,
            ]) ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="bc">
            <?= $this->render('_bank_cards', [
                'model' => $model,
                'dpBankCards' => $dpBankCards,
            ]) ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="drivers">
            <?= $this->render('_drivers', [
                'model' => $model,
                'dpDrivers' => $dpDrivers,
            ]) ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="transport">
            <?= $this->render('_transport', [
                'model' => $model,
                'dpTransport' => $dpTransport,
            ]) ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="payment_orders">
            <?= $this->render('_payment_orders', ['dataProvider' => $dpPaymentOrders, 'totalAmount' => $poTotalAmount]); ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="freights">
            <?= $this->render('_freights', ['dataProvider' => $dpOrders, 'totalAmount' => $ordersTotalAmount]); ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="files">
            <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

            <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('logist')): ?>
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

        </div>
    </div>
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
