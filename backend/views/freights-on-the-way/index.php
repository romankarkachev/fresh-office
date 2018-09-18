<?php

use yii\helpers\Url;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Транспорт в пути | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Транспорт в пути';

$preloader = '<i class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>';
?>
<div class="freights-on-the-way-list">
    <?php if ($dataProvider->totalCount > 0): ?>
    <p>Всего транспортных средств в очереди: <?= $dataProvider->totalCount ?>.</p>
    <?php endif; ?>
    <?= GridView::widget([
        'id' => 'gwQueue',
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'id',
            [
                'attribute' => 'state_acquired_at',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'address',
            'destination',
            'data',
            'ferryman',
            [
                'attribute' => 'remain_text',
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    return ['id' => 'rt' . $model['id'], 'class' => 'text-center'];
                },
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'arriving_at',
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    return ['id' => 'aat' . $model['id'], 'class' => 'text-center'];
                },
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'unload_at',
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    return ['id' => 'uat' . $model['id'], 'class' => 'text-center'];
                },
                'headerOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<?php
$urlCalculateDuration = Url::to(['/freights-on-the-way/get-duration-for-route']);
$this->registerJs(<<<JS
var requestsSent = -1;
var requestsReturned = -1;

// Функция выполняет сортировку таблицы по значениям свойств "data-sort" строк.
//
function sortTable() {
    var \$tbody = $("#gwQueue tbody");
    \$tbody.find('tr').sort(function (a, b) {
        var tda = parseInt($(a).attr("data-sort"));
        var tdb = parseInt($(b).attr("data-sort"));

        return tda > tdb ? 1 : tda < tdb ? -1 : 0;
    }).appendTo( \$tbody )
} // sortTable()

// Функция запрашивает расстояния по данным всех строк, выведенных в таблицу.
//
function calculateDuration() {
    $("tr").each(function() {
        id = $(this).attr("data-key");
        if (id != undefined && id != "") {
            requestsSent++;
            \$block = $("#rt" + id);
            \$blockUat = $("#uat" + id);
            \$blockAat = $("#aat" + id);
            \$block.html('$preloader');
            //\$blockUat.html('$preloader');
            //\$blockAat.html('$preloader');
            $.get("$urlCalculateDuration?project_id=" + id + "&iterator=" + requestsSent, function(retval) {
                requestsReturned++;
                if (retval != false) {
                    \$block = $("#rt" + retval.project_id);
                    \$blockUat = $("#uat" + retval.project_id);
                    \$blockAat = $("#aat" + retval.project_id);
                    \$block.html(retval.remain_text);
                    if (retval.arriving_at != "") \$blockUat.html(retval.unload_at);
                    \$blockAat.html(retval.arriving_at);

                    $("tr[data-key='" + retval.project_id + "']").attr("data-sort", retval.unload_sort);
                    // если количество отправленных запросов и полученных ответов совпало, то сортируем таблицу
                    if (requestsReturned == requestsSent) sortTable();
                }
            });
        }
    });
} // calculateDuration()

calculateDuration();
JS
, \yii\web\View::POS_READY);
?>
