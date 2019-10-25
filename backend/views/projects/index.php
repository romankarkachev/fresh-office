<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\controllers\ProjectsController;
use common\models\ProjectsStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */

$this->title = 'Проекты | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Проекты';
?>
<div class="projects-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-truck"></i> Назначить перевозчика', '#', ['class' => 'btn btn-default pull-right', 'id' => 'btn-assign-ferryman']) ?>

        <?= Html::a('<i class="fa fa-money" aria-hidden="true"></i> Подать в оплату', '#', ['class' => 'btn btn-default pull-right', 'id' => 'btn-create-order']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover table-responsive'],
        'id' => 'gw-projects',
        'rowOptions' => function($model) {
            $options = ['data-amount' => $model->cost];

            switch ($model->state_id) {
                case ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА:
                    $options['class'] = 'warning';
                    break;
                case ProjectsStates::STATE_ТРАНСПОРТ_ЗАКАЗАН:
                    $options['class'] = 'success';
                    break;
            }

            return $options;
        },
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'options' => ['width' => '30'],
            ],
            'id',
            [
                'attribute' => 'type_name',
                //'options' => ['width' => '90'],
                //'headerOptions' => ['class' => 'text-center'],
                //'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'state_name',
                //'options' => ['width' => '90'],
                //'headerOptions' => ['class' => 'text-center'],
                //'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'vivozdate',
                'format' =>  ['date', 'dd.MM.YYYY'],
            ],
            //'date_start:datetime',
            //'date_end:datetime',
            'ca_name',
            'manager_name',
            [
                'attribute' => 'amount',
                'format' => ['decimal', 'decimals' => 2],
                'options' => ['width' => '110'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'cost',
                'format' => ['decimal', 'decimals' => 2],
                'options' => ['width' => '110'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'perevoz',
            [
                'attribute' => 'oplata',
                'format' =>  ['date', 'dd.MM.YYYY'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{exportWasteReminder} {createFerrymanOrder} {generateTtn} {generateApp} {dropProductionFiles}',
                'buttons' => [
                    'exportWasteReminder' => function ($url, $model) {
                        return Html::a('<i class="fa fa-hotel"></i>', '#', [
                            'id' => 'btnExportWasteReminder' . $model->id,
                            'data-id' => $model->id,
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Отправить клиенту напоминание о вывозе',
                        ]);
                    },
                    'createFerrymanOrder' => function ($url, $model) {
                        return Html::a('<i class="fa fa-file-text-o"></i>', '#', [
                            'id' => 'btnCreateFerrymanOrder' . $model->id,
                            'data-id' => $model->id,
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Создать заявку для перевозчика',
                        ]);
                    },
                    'generateTtn' => function ($url, $model) {
                        return Html::a('<i class="fa fa-book text-primary"></i>', ['/projects/generate-document', 'doc_type' => ProjectsController::GENERATE_DOCUMENT_TTN, 'project_id' => $model->id], [
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Сгенерировать товарно-транспортную накладную',
                            'target' => '_blank',
                        ]);
                    },
                    'generateApp' => function ($url, $model) {
                        return Html::a('<i class="fa fa-book"></i>', ['/projects/generate-document', 'doc_type' => ProjectsController::GENERATE_DOCUMENT_APP, 'project_id' => $model->id], [
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Сгенерировать акт приема-передачи',
                            'target' => '_blank',
                        ]);
                    },
                    'dropProductionFiles' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash text-danger"></i>', ['/projects/drop-production-files', 'id' => $model->id], [
                            'id' => 'btnDropProductionFiles' . $model->id,
                            'data' => [
                                'id' => $model->id,
                                'method' => 'post',
                                'pjax' => '0',
                                'confirm' => 'Вы действительно хотите удалить файлы от производства по этому проекту?',
                            ],
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Удалить файлы с производства по этому проекту',
                        ]);
                    },
                ],
                'visibleButtons' => [
                    'dropProductionFiles' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<div id="mw_summary" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Modal title</h4>
            </div>
            <div id="modal_body" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn-process"><i class="fa fa-cog"></i> Выполнить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<div id="totalAmountPreview"></div>
<?php
$this->registerCss(<<<CSS
#totalAmountPreview {
    width: 200px;
    border: 1px solid #ccc;
    background: #f7f7f7;
    text-align: center;
    padding: 5px;
    position: fixed;
    bottom: 10px;
    right: 10px;
    cursor: pointer;
    display: none;
    color: #333;
    font-size: 14px;
}
CSS
);

$urlCreatePaymentOrderBySelection = Url::to(['/projects/create-order-by-selection']);
$url_form = Url::to(['/projects/assign-ferryman-form']);
$url_fields = Url::to(['/projects/compose-ferryman-fields']);
$url_process = Url::to(['/projects/assign-ferryman']);

$urlFerrymanOrderForm = Url::to(['/projects/ferryman-order-form']);
$urlExportWasteReminderForm = Url::to(['/projects/export-waste-reminder-form']);
$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Перевозчик".
//
function ferrymanOnChange(type, ferryman_id) {
    if (ferryman_id != 0 && ferryman_id != "" && ferryman_id != undefined) {
        $("#block-fields").html("<p class=\"text-center\"><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i><span class=\"sr-only\">Подождите...</span></p>");
        $("#block-fields").load("$url_fields?type=" + type + "&model_id=" + ferryman_id);
    }
} // ferrymanOnChange()

// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    \$button = $("#btnSearch");
    \$button.attr("disabled", "disabled");
    text = \$button.text();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.text(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
var checked = false;
$("input[type='checkbox']").iCheck({checkboxClass: 'icheckbox_square-green'});

// Обработчик щелчка по ссылке "Отметить все проекты".
//
function checkAllProjectsOnClick() {
    if (confirm("Выделение большого количества проектов может занять продолжительное время. 300 проектов отмечаются в течение 50 секунд. Продолжить?")) {
        if (checked) {
        operation = "uncheck";
        checked = false;
        }
        else {
            operation = "check";
            checked = true;
        }
    
        $("input[name ^= 'selection[]']").iCheck(operation);
    }

    return false;
} // checkAllProjectsOnClick()

// Обработчик щелчка по кнопке "Подать в оплату".
//
function createOrderOnClick() {
    var ids = $("#gw-projects").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $.get("$urlCreatePaymentOrderBySelection?ids=" + ids);
    return false;
} // createOrderOnClick()

// Функция-обработчик щелчка по кнопке Назначить перевозчика.
// Отображает форму назначения.
//
function assignFerrymanFormOnClick() {
    var ids = $("#gw-projects").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $("#modal_title").text("Назначение перевозчика в проекты");
    $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
    $("#mw_summary").modal();
    $("#modal_body").load("$url_form?ids=" + ids);

    return false;
} // assignFerrymanFormOnClick()

// Функция-обработчик щелчка по кнопке Выполнить.
// Выполняет назначение перевозчика в выбранные проекты.
//
function assignFerrymanOnClick() {
    \$form = $("#frmAssignFerryman");
    if (\$form.length == 0) \$form = $("#frmExportWasteReminder");
    if (\$form.length != 0) \$form.submit();

    return false;
} // assignFerrymanOnClick()

// Обработчик щелчка на кнопкам "Создать заявку для перевозчика" в списке проектов.
// Отображает форму создания заявки по шаблону.
//
function createFerrymanOrderFormOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && id != undefined) {
        $("#modal_title").text("Создание заявки для перевозчика по проекту " + id);
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_summary").modal();
        $("#modal_body").load("$urlFerrymanOrderForm?id=" + id);
    }

    return false;
} // createFerrymanOrderFormOnClick()

// Обработчик щелчка по кнопкам "Отправить клиенту напоминание о вывозе" в списке проектов.
//
function exportWasteReminderOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && id != undefined) {
        $("#modal_title").text("Отправка клиенту напоминания о вывозе (проект " + id + ")");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_summary").modal();
        $("#modal_body").load("$urlExportWasteReminderForm?id=" + id);
    }

    return false;
} // exportWasteReminderOnClick()

// Обработчик щелчка отметки в любой строке.
//
function checkRowOnChange() {
    amount = 0;
    $("input[name ^= 'selection']:checked").each(function(index, value) {
        current = parseFloat($("tr[data-key = '" + $(value).val() + "']").attr("data-amount"));
        if (!isNaN(current)) amount += current;
    });

    \$blockAmount = $("#totalAmountPreview");
    if (amount == 0) {
        \$blockAmount.html("");
        \$blockAmount.fadeOut();
    }
    else {
        amount = new Intl.NumberFormat('ru-RU').format(amount);
        \$blockAmount.html("Выделено проектов на сумму: <strong>" + amount + "</strong> руб.");
        \$blockAmount.fadeIn();
    }
} // checkRowOnChange()

$(".select-on-check-all").on("ifClicked", checkAllProjectsOnClick);
$("input[name ^= 'selection']").on("ifChanged", checkRowOnChange);
$(document).on("click", "#btn-create-order", createOrderOnClick);
$(document).on("click", "#btn-assign-ferryman", assignFerrymanFormOnClick);
$(document).on("click", "#btn-process", assignFerrymanOnClick);
$(document).on("click", "a[id ^= 'btnCreateFerrymanOrder']", createFerrymanOrderFormOnClick);
$(document).on("click", "a[id ^= 'btnExportWasteReminder']", exportWasteReminderOnClick);
JS
, \yii\web\View::POS_READY);
?>
