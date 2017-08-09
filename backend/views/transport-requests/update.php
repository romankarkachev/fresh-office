<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;
use common\models\TransportRequestsDialogs;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */
/* @var $waste common\models\TransportRequestsWaste[] */
/* @var $transport common\models\TransportRequestsTransport[] */
/* @var $dpDialogs \yii\data\ActiveDataProvider */
/* @var $dpPrivateDialogs \yii\data\ActiveDataProvider */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$this->title = '№ ' . $model->id . ' от ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y') . HtmlPurifier::process(' &mdash; Запросы на транспорт | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']];
$this->params['breadcrumbs'][] = $model->representation . ' (автор: ' . $model->createdByName . ')';

$newMessage = new TransportRequestsDialogs();
$newMessage->tr_id = $model->id;
$newMessage->created_by = Yii::$app->user->id;

$favoriteGs = '/images/favorite24gs.png';
$favorite = '/images/favorite24.png';
?>
<div class="transport-requests-update">
    <div class="panel with-nav-tabs panel-success">
        <div class="panel-heading">
            <ul class="nav nav-pills" role="tablist">
                <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
                <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab" data-id="<?= $model->id ?>">Диалоги<?= $model->messagesUnread == 0 ? '' : ' <strong id="messages-count">' . $model->messagesUnread . '</strong>' ?></a></li>
                <?php if (Yii::$app->user->can('logist') || Yii::$app->user->can('root')): ?>
                <li role="presentation"><a href="#privatemessages" aria-controls="privatemessages" role="tab" data-toggle="tab" data-id="<?= $model->id ?>"><i class="fa fa-user-secret" aria-hidden="true"></i> Приватные диалоги<?= $model->privateMessagesUnread == 0 ? '' : ' <strong id="privatemessages-count">' . $model->privateMessagesUnread . '</strong>' ?></a></li>
                <?php endif; ?>
                <li role="presentation"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Файлы</a></li>
                <li role="presentation"><a href="#help" aria-controls="help" role="tab" data-toggle="tab"><i class="fa fa-info-circle" aria-hidden="true"></i> Подсказка</a></li>
                <li class="pull-right"><?= Html::a(Html::img($model->is_favorite == true ? $favorite : $favoriteGs), '#', ['id' => 'btnFavorite', 'data-id' => $model->id]) ?></li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content" style="padding: 10px;">
                <div role="tabpanel" class="tab-pane fade in active" id="common">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'waste' => $waste,
                        'transport' => $transport,
                    ]) ?>

                </div>
                <div role="tabpanel" class="tab-pane fade" id="messages">
                    <?= $this->render('_dialogs', [
                        'dataProvider' => $dpDialogs,
                        'model' => $newMessage,
                        'action' => 'add-dialog-message',
                    ]); ?>

                </div>
                <div role="tabpanel" class="tab-pane fade" id="privatemessages">
                    <?= $this->render('_dialogs', [
                        'dataProvider' => $dpPrivateDialogs,
                        'model' => $newMessage,
                        'action' => 'add-private-dialog-message',
                    ]); ?>

                </div>
                <div role="tabpanel" class="tab-pane fade" id="files">
                    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

                    <?= FileInput::widget([
                        'id' => 'new_files',
                        'name' => 'files[]',
                        'options' => ['multiple' => true],
                        'pluginOptions' => [
                            'maxFileCount' => 10,
                            'uploadAsync' => false,
                            'uploadUrl' => Url::to(['/transport-requests/upload-files']),
                            'uploadExtraData' => [
                                'obj_id' => $model->id,
                            ],
                        ]
                    ]) ?>

                </div>
                <div role="tabpanel" class="tab-pane fade" id="help">
                    <?= $this->render('_help') ?>

                </div>
            </div>
        </div>
    </div>
    <div id="mw_summary" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-info" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal_title" class="modal-title">Modal title</h4>
                    <small id="modal_title_right" class="form-text"></small>
                </div>
                <div id="modal_body" class="modal-body">
                    <p>One fine body…</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$publicAttr = TransportRequestsDialogs::DIALOGS_PUBLIC;
$privateAttr = TransportRequestsDialogs::DIALOGS_PRIVATE;

$url_toggle = Url::to(['/transport-requests/toggle-favorite']);
$url_mark_read = Url::to(['/transport-requests/mark-as-read']);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Избранный". Выполняет переключение этого признака.
//
function btnFavoriteOnClick() {
    \$btn = $(this);
    \$btn.html("<i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i>");
    id = \$btn.attr("data-id");
    if (id != "" && id != undefined)
        $.get("$url_toggle?id=" + id, function(result) {
            if (result == true)
                \$btn.html("<img src=\"$favorite\" />");
            else
                \$btn.html("<img src=\"$favoriteGs\" />");
        });

    return false;
} // btnFavoriteOnClick()

$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href");
    if ((target == '#messages')) {
        id = $(this).attr("data-id");
        if (id != "" && id != undefined && $("#messages-count").length > 0)
            $.get("$url_mark_read?id=" + id + "&private=" + $publicAttr, function(result) {
                $("#messages-count").remove();
            });
    }
    else if ((target == '#privatemessages')) {
        id = $(this).attr("data-id");
        if (id != "" && id != undefined && $("#privatemessages-count").length > 0)
            $.get("$url_mark_read?id=" + id + "&private=" + $privateAttr, function(result) {
                $("#privatemessages-count").remove();
            });
    }
});

var url = window.location.href;
var activeTab = url.substring(url.indexOf("#") + 1);
$('a[href="#'+ activeTab +'"]').tab("show");

$(document).on("click", "#btnFavorite", btnFavoriteOnClick);
JS
, \yii\web\View::POS_READY);
?>
