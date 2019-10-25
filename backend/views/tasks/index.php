<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use backend\components\grid\GridView;
use backend\controllers\TasksController;
use common\models\TasksSearch;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TasksSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TasksController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksController::ROOT_LABEL;

$mwPrompt = 'Задача';

$columns = [
    [
        'attribute' => 'created_at',
        'label' => 'Создана',
        'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'options' => ['width' => '130'],
    ],
];

if ($searchModel->searchSource == TasksSearch::TASK_SOURCE_WEB_APP) {
    $columns = \yii\helpers\ArrayHelper::merge($columns, [
        [
            'attribute' => 'createdByProfileName',
            'label' => 'Автор',
        ],
    ]);
}

$columns = \yii\helpers\ArrayHelper::merge($columns, [
    'responsibleProfileName',
    'typeName',
    'stateName',
    //'priorityName',
    'purpose:ntext',
    'solution:ntext',
    [
        'attribute' => 'start_at',
        'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'options' => ['width' => '130'],
    ],
    [
        'attribute' => 'finish_at',
        'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'options' => ['width' => '130'],
    ],
    [
        'attribute' => 'postponedCount',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
            /* @var $model \common\models\Tasks */
            /* @var $column \yii\grid\DataColumn */

            $result = $model->{$column->attribute};
            if (empty($result)) {
                return '-';
            }
            else {
                return $result;
            }
        },
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'options' => ['width' => '60'],
    ],
    //'fo_ca_id',
    //'fo_ca_name',
    //'fo_cp_id',
    //'fo_cp_name',
    //'responsible_id',
    //'project_id',
]);
if ($searchModel->searchSource == TasksSearch::TASK_SOURCE_WEB_APP) {
    $columns[] = ['class' => 'backend\components\grid\ActionColumn'];
}

$formName = strtolower($searchModel->formName());
?>
<div class="tasks-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'gw-tasks',
        'dataProvider' => $dataProvider,
        'rowOptions' => function ($model, $key, $index, $grid) use ($searchModel) {
            /* @var $model \common\models\Tasks|\common\models\foTasks */
            $result['data-id'] = $model->id;

            if ((
                $searchModel->searchSource == TasksSearch::TASK_SOURCE_WEB_APP && $model->state_id == \common\models\foTasksStates::STATE_В_ПРОЦЕССЕ
                ) || (
                $searchModel->searchSource == TasksSearch::TASK_SOURCE_FO && $model->state_id == \common\models\foTasksStates::STATE_ВЫПОЛНЕНА
                )
            ) {
                $result['class'] = 'success';
            }

            return $result;
        },
        'columns' => $columns,
    ]); ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="mwTitle" class="modal-title"></h4></div>
            <div id="mwBody" class="modal-body"><p>One fine body…</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button></div>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<JS
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
    }, 1000);
}
JS
, View::POS_BEGIN);

switch ($searchModel->searchSource) {
    case TasksSearch::TASK_SOURCE_WEB_APP:
        $url = Url::to(TasksController::URL_TASK_POSTPONEMENT_AS_ARRAY);
        $this->registerJs(<<<JS
$("#gw-tasks tbody tr").css("cursor", "pointer");
$("#gw-tasks tbody td").click(function (e) {
    var id = $(this).closest("tr").data("id");
    if (e.target == this && id) {
        \$body = $("#mwBody");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mwTitle").html("$mwPrompt #" + id);
        $("#modalWindow").modal();
        \$body.load("$url?id=" + id);
    }
});
JS
        , View::POS_READY);
        break;
    case TasksSearch::TASK_SOURCE_FO:
        $url = Url::to(TasksController::URL_RENDER_TASK_SUMMARY_AS_ARRAY);
        $this->registerJs(<<<JS
$("#gw-tasks tbody tr").css("cursor", "pointer");
$("#gw-tasks tbody td").click(function (e) {
    var id = $(this).closest("tr").data("id");
    if (e.target == this && id) {
        \$body = $("#mwBody");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mwTitle").html("$mwPrompt #" + id);
        $("#modalWindow").modal();
        \$body.load("$url?id=" + id);
    }
});
JS
        , View::POS_READY);
        break;
}

$urlPostpone = Url::to(TasksController::URL_TASK_POSTPONEMENT_AS_ARRAY);
$urlRefillResponsible = Url::to(TasksController::URL_REFILL_RESPONSIBLE_AS_ARRAY);
$this->registerJs(<<<JS

// Обработчик изменения источника данных для задач.
//
function searchSourceOnChange() {
    \$field = $("#$formName-responsible_id");
    \$field.empty().trigger("change");

    source_id = $("input[name='TasksSearch[searchSource]']:checked").val();
    $.get("$urlRefillResponsible?source_id=" + source_id, function (response) {
        $.each(response, function(index, value) {
            var newOption = new Option(value, index, true, true);
            \$field.append(newOption);
        });
        \$field.val(null).trigger("change");
    });
} // searchSourceOnChange()

// Обработчик щелчка по кнопке "Перенести" в форме переноса задачи.
//
function btnPostponeOnClick() {
    $.post("$urlPostpone", $("#frmPostpone").serialize(), function (response) {
        if (response == true) {
            $("#modalWindow").modal("hide");
            alert("Задача перенесена успешно.");
        }
        else {
            alert("Не удалось перенести задачу.");
        }
    });
} // btnPostponeOnClick()

$(document).on("change", "#$formName-searchsource", searchSourceOnChange);
$(document).on("click", "#btnPostpone", btnPostponeOnClick);
JS
, View::POS_READY);
?>
