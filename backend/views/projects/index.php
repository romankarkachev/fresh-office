<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

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
        'id' => 'gw-projects',
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
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
            'date_start:datetime',
            'date_end:datetime',
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
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'visible' => false,
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
<?php
$urlCreateOrderBySelection = Url::to(['/projects/create-order-by-selection']);
$url_form = Url::to(['/projects/assign-ferryman-form']);
$url_fields = Url::to(['/projects/compose-ferryman-fields']);
$url_process = Url::to(['/projects/assign-ferryman']);
$this->registerJs(<<<JS
function ferrymanOnChange() {
    $("#block-fields").html("<p class=\"text-center\"><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i><span class=\"sr-only\">Подождите...</span></p>");
    ferryman_id = $("#assignferrymanform-ferryman_id").val();
    if (ferryman_id != 0 && ferryman_id != "" && ferryman_id != undefined)
        $("#block-fields").load("$url_fields?ferryman_id=" + ferryman_id);
} // ferrymanOnChange()
JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Подать в оплату".
//
function createOrderOnClick() {
    var ids = $("#gw-projects").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $.get("$urlCreateOrderBySelection?ids=" + ids);
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
    $("#frmAssignFerryman").submit();

    return false;
} // assignFerrymanOnClick()

$(document).on("click", "#btn-create-order", createOrderOnClick);
$(document).on("click", "#btn-assign-ferryman", assignFerrymanFormOnClick);
$(document).on("click", "#btn-process", assignFerrymanOnClick);
JS
, \yii\web\View::POS_READY);
?>
