<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\EdfController;
use common\models\EdfStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EdfSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */
/* @var $isFilterDs bool */

$this->title = EdfController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = EdfController::ROOT_LABEL;

$gridViewId = 'gw-edf';
$btnDeleteFewPrompt = '<i class="fa fa-trash-o" aria-hidden="true"></i> удалить выбранные';
$btnDeleteFewId = 'deleteSelected';
$btnDeleteFew = '<div class="col-md-6">' . Html::a($btnDeleteFewPrompt, '#', ['id' => $btnDeleteFewId, 'class' => 'btn btn-danger btn-xs', 'title' => 'Удалить выделенные электронные документы']) . '</div>';
?>
<div class="edf-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', EdfController::ROOT_URL_FOR_SORT_PAGING . '?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <?= GridView::widget([
        'id' => $gridViewId,
        'dataProvider' => $dataProvider,
        'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small></div>\n{items}\n<div class=\"row\">$btnDeleteFew<div class=\"col-md-6\"><small class=\"pull-right form-text text-muted\">{summary}</small></div></div>\n{pager}",
        'rowOptions' => function ($model, $key, $index, $grid) {
            /* @var $model \common\models\Edf */

            $options = [];
            switch ($model->state_id) {
                case EdfStates::STATE_ОТКАЗ:
                    $options = ['class' => 'danger'];
                    break;
                case EdfStates::STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА:
                case EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА:
                    $options = ['class' => 'info'];
                    break;
                case EdfStates::STATE_СОГЛАСОВАНИЕ:
                    $options = ['class' => 'warning'];
                    break;
                case EdfStates::STATE_ПОДПИСАН_РУКОВОДСТВОМ:
                    $options = ['class' => 'success'];
                    break;
                case EdfStates::STATE_ЗАВЕРШЕНО:
                    $options = ['style' => 'background-color:#eee;'];
                    break;
            }

            return $options;
        },
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'options' => ['width' => '30'],
                'visible' => Yii::$app->user->can('root'),
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'managerProfileName',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Edf */
                    /* @var $column \yii\grid\DataColumn */

                    $newMessages = '';
                    if ($model->unreadMessagesCount > 0)
                        $newMessages = ' <i class="fa fa-commenting text-primary" aria-hidden="true" title="Новых сообщений: ' . $model->unreadMessagesCount . '"></i>';

                    return $model->{$column->attribute} . $newMessages;
                },
                //'visible' => !Yii::$app->user->can('sales_department_manager'),
            ],
            [
                'attribute' => 'stateName',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Edf */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty($model->stateChangedAt)) {
                        return Html::tag('abbr', $model->{$column->attribute}, ['title' => 'Статус приобретен ' . Yii::$app->formatter->asDate($model->stateChangedAt, 'php:d.m.Y в H:i')]);
                    }
                    else {
                        return $model->{$column->attribute};
                    }
                },
            ],
            [
                'attribute' => 'typeName',
                'visible' => !$isFilterDs,
            ],
            [
                'attribute' => 'contractTypeName',
                'visible' => !$isFilterDs,
            ],
            'req_name_short',
            'organizationName',
            'doc_num',
            [
                'attribute' => 'parentRep',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Edf */
                    /* @var $column \yii\grid\DataColumn */

                    return $model->{$column->attribute};
                },
                'visible' => $isFilterDs,
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head'),
                ],
            ],
        ],
    ]); ?>

</div>
<?php
$urlDeleteFew = \yii\helpers\Url::to(EdfController::URL_DELETE_SELECTED_AS_ARRAY);

$this->registerJs(<<<JS

var checked = false;
$("input[type='checkbox']").iCheck({checkboxClass: 'icheckbox_square-green'});

// Выполняет пересчет количества выделенных пользователем документов и подставляет отличное от нуля значение в текст кнопки.
//
function recountSelected() {
    var count = $("input[name ^= 'selection[]']:checked").length;
    var prompt = "";
    var promptDelete = '$btnDeleteFewPrompt';
    if (count > 0) {
        prompt = " <strong>(" + count + ")</strong>";
        promptDelete += prompt;
    }

    $("#$btnDeleteFewId").html(promptDelete);
} // recountSelected()

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
    recountSelected();

    return false;
} // checkAllOnClick()

// Обработчик щелчка по ссылке "Удалить выделенные документы".
//
function deleteSelectedOnClick() {
    var ids = $("#$gridViewId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    if (confirm("Вы действительно хотите удалить выделенные электронные документы безвозвратно?")) {
        $.post("$urlDeleteFew", {ids: ids}, function() {});
    }

    return false;
} // deleteSelectedOnClick()

$("input[name ^= 'selection[]']").on("ifChanged", recountSelected);
$(".select-on-check-all").on("ifClicked", checkAllOnClick);
$(document).on("click", "#$btnDeleteFewId", deleteSelectedOnClick);
JS
, \yii\web\View::POS_READY);
?>