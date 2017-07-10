<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;
use common\models\TransportRequestsDialogs;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */
/* @var $waste common\models\TransportRequestsWaste[] */
/* @var $transport common\models\TransportRequestsTransport[] */
/* @var $dpDialogs \yii\data\ActiveDataProvider */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$this->title = '№ ' . $model->id . ' от ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y') . HtmlPurifier::process(' &mdash; Запросы на транспорт | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']];
$this->params['breadcrumbs'][] = $model->representation . ' (автор: ' . $model->createdByName . ')';

$newMessage = new TransportRequestsDialogs();
$newMessage->tr_id = $model->id;
$newMessage->created_by = Yii::$app->user->id;
?>
<div class="transport-requests-update">
    <div class="panel with-nav-tabs panel-success">
        <div class="panel-heading">
            <ul class="nav nav-pills" role="tablist">
                <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
                <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Диалоги</a></li>
                <li role="presentation"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Файлы</a></li>
                <li role="presentation"><a href="#help" aria-controls="help" role="tab" data-toggle="tab"><i class="fa fa-info-circle" aria-hidden="true"></i> Подсказка</a></li>
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
                    ]); ?>

                </div>
                <div role="tabpanel" class="tab-pane fade" id="files">
    <!--<div class="page-header"><h3>Файлы</h3></div>-->
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
                    <p><strong>Общая информация.</strong> Автором запроса является авторизованный пользователь, который его создал. Изменение авторства в течение жизни запроса не предусмотрено. Менеждер видит в списке только собственные запросы, логист - все.</p>
                    <p>
                        <strong>Табличная часть &laquo;Отходы&raquo;</strong>. Нажмите кнопку &laquo;Добавить строку&raquo;. Обязательно необходимо
                        заполнить поле &laquo;Наименование отходов&raquo;, &laquo;Единица измерения&raquo;, &laquo;Количество&raquo;. Текст в поле &laquo;Наименование
                        отходов&raquo; вводится всегда вручную, но если система выдает в результате выборки подходящий отход,
                        то Вы должны выбрать его обязательно. Не выбирать отход можно только в том случае, если он
                        отсутствует в справочнике или из предложенных вариантов ни один не подходит. Текст в поле
                        &laquo;Вид упаковки&raquo; тоже всегда вводится вручную, но при работе с этим полем необходимо быть внимательным,
                        поскольку если Вы напишете &laquo;картон&raquo;, но не выберете из списка предложенных &laquo;Картон&raquo;, то в
                        справочнике видов упаковки будет создан еще один элемент &laquo;картон&raquo;, что недопустимо. А после
                        этого, когда снова начнете набирать слово &laquo;картон&raquo; в другой строке табличной части,
                        система предложит уже два варианта - &laquo;картон&raquo; и
                        &laquo;Картон&raquo;. Такого допускать нельзя. Всегда выполняйте выбор предлагаемых элементов, если среди
                        них есть подходящий. Количество вводится всегда только целыми числами, поэтому, если необходимо
                        ввести граммы, а есть только тонны, то необходимо заблаговременно создать соответствующую
                        единицу измерения и вводить ее меру только целым числом.
                    </p>
                    <p>
                        <strong>Табличная часть &laquo;Транспорт&raquo;</strong>. Нажмите кнопку &laquo;Добавить строку&raquo;. Табличную часть наполняет менеджер,
                        логист дополняет стоимостью. Если при наполнении этой табличной части система автоматически
                        подставляет стоимость, это поведение считается нормальным. Когда логист откроет запрос, он увидит,
                        что стоимость необходимо либо утвердить (не менять) либо изменит ее по своему усмотрению. Утверждение
                        цены &mdash; это просто закрытие запроса через установленную галочку внизу формы на закладке
                        Общие.
                    </p>
                    <p>
                        <strong>Закладка &laquo;Диалоги&raquo;</strong>. Добавление комментария и сортировка таблицы
                        осуществяется по технологии &laquo;pjax&raquo;, без перезагрузки страницы. Но автоматическое
                        обновление списка сообщений не предусмотрена. Для этих целей применяется обыкновенная перезагрузка
                        страницы.
                    </p>
                    <p>
                        <strong>Прочие замечания. </strong>
                        Закрытую заявку можно вернуть в статус &laquo;В обработке&raquo;, если добавить любой комментарий.
                    </p>
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
$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});
JS
, \yii\web\View::POS_READY);
?>
