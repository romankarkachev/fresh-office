<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\controllers\EcoContractsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoMcSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $reportsColumns array динамические колонки */

$this->title = EcoContractsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoContractsController::ROOT_LABEL;

$dataTableId = 'gw-contracts';
$preloader = '<i class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>';
?>
<div class="eco-mc-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <div id="header"></div>
    <?= GridView::widget([
        'tableOptions' => [
            'id' => $dataTableId,
            'class' => 'table table-striped table-hover table-responsive',
        ],
        'dataProvider' => $dataProvider,
        'columns' => ArrayHelper::merge(ArrayHelper::merge([
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'createdByProfileName',
                'visible' => false,
            ],
            [
                'attribute' => 'fo_ca_id',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) use ($preloader) {
                    /* @var $model \common\models\EcoMc */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty(trim($model->comment))) {
                        return Html::tag('abbr', $preloader, ['id' => 'focaName' . $model->id, 'data-focaid' => $model->fo_ca_id, 'title' => $model->comment]);
                    }
                    else {
                        return Html::tag('span', $preloader, ['id' => 'focaName' . $model->id, 'data-focaid' => $model->fo_ca_id]);
                    }

                    // до 16.12.19 было так:
                    //return Html::tag('span', $preloader, ['id' => 'focaName' . $model->id, 'data-focaid' => $model->fo_ca_id]);
                },
            ],
            'managerProfileName',
            [
                'attribute' => 'amount',
                'format' => 'decimal',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-nowrap text-right'],
            ],
            [
                'attribute' => 'reports',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoMc */
                    /* @var $column \yii\grid\DataColumn */

                    return nl2br($model->{$column->attribute});
                },
                'visible' => false,
            ],
            [
                'attribute' => 'date_start',
                'label' => 'Действует с',
                'format' => 'date',
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'visible' => false,
            ],
            [
                'attribute' => 'date_finish',
                'label' => 'Действует по',
                'format' => 'date',
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'visible' => false,
            ],
            //'comment:ntext',
        ], $reportsColumns), [
            ['class' => 'backend\components\grid\ActionColumn'],
        ]),
    ]); ?>

</div>
<?php
$urlEvaluateNames = Url::to(EcoContractsController::URL_EVALUATE_CA_NAMES_AS_ARRAY);
$urlSubmitReport = Url::to(EcoContractsController::URL_SUBMIT_REPORT_AS_ARRAY);

$this->registerJsFile('https://unpkg.com/sticky-table-headers', ['depends' => 'yii\web\JqueryAsset', 'position' => \yii\web\View::POS_END]);
$this->registerCss(<<<CSS
.tableFloatingHeaderOriginal { background-color: #eaeaea; }
.tableFloatingHeaderOriginal th { vertical-align: middle !important; }
CSS
);

$this->registerJs(<<<JS
// Функция вычисляет наименования контрагентов, выведенных на экране и подставляет их в соответствующие месте на странице.
//
function evaluateCaNames() {
    var focas = [];

    $("[id^='focaName']").each(function(index) {
        var fo_ca_id = $(this).attr("data-focaid");
        if ($.inArray(fo_ca_id, focas) === -1) focas.push(fo_ca_id);
    });

    $.get("$urlEvaluateNames?ids=" + focas, function(response) {
        $.each(response, function(index, element) {
            $("[data-focaid='" + element.id + "']").text(element.name);
        });
    });
} // evaluateCaNames()

// Обработчик щелчка по ссылкам "Сдать отчет" в списке договоров сопровождения.
//
function submitReportOnClick() {
    var id = $(this).attr("data-id");
    $(this).hide();
    $("#block-submitDate" + id).show();

    return false;
} // submitReportOnClick()

// Обработчик изменения значения в любом из полей с датой сдачи отчетов.
//
function submitDate(id, date) {
    $.post("$urlSubmitReport", {id: id, date: date}, function(response) {
        if (response == true) {
            alert("Дата сдачи отчета успешно установлена!");
        }
        else {
            alert("Не удалось установить дату подачи отчета!");
        }
    });
} // submitDateOnChange()

// Обработчик изменения даты в любой строке таблицы (для быстрой установки даты сдачи отчета).
//
function submitDateOnChange(e, id) {
    if (confirm("Будет установлена дата фактической подачи отчета в контролирующие органы. Продолжить?")) submitDate(id, e.format("yyyy-mm-dd"));
} // submitDateOnChange()

// вычисляем наименования контрагентов
evaluateCaNames();

// применяем компонент для фиксации заголовка таблицы при прокручивании страницы
\$dataTable = $("#$dataTableId");
\$dataTable.stickyTableHeaders({fixedOffset: $("#navbar-collapse"), cacheHeaderHeight: true});

$(document).on("click", "a[id ^= 'submitReport']", submitReportOnClick);
JS
, \yii\web\View::POS_READY);
?>
