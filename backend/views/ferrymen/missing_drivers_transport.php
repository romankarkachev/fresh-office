<?php

use yii\helpers\Url;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FerrymenSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Проверка наличия перевозчиков, водителей и транспорта в проектах в CRM и веб-приложении | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отсутствующие водители и транспорт';

$preloader = '<i class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>';
$iconOk = '<i class="fa fa-check-circle text-success" aria-hidden="true"></i>';
$iconCancel = '<i class="fa fa-times text-warning" aria-hidden="true"></i>';
$iconUndefined = '<i class="fa fa-question" aria-hidden="true" style="color: #cecece;"></i>';
?>
<div class="ferrymen-missing-drivers-transport-list">
    <?= $this->render('_search_mdt', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small></div>\n{items}\n{pager}",
        'summary' => "Показаны записи с <strong>{begin}</strong> по <strong>{end}</strong>, на странице <strong>{count}</strong>, всего <strong>{totalCount}</strong>. Страница <strong>{page}</strong> из <strong>{pageCount}</strong>.",
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['width' => '130'],
            ],
            'data',
            [
                'attribute' => 'ferryman',
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    return ['id' => 'ferryman' . $model['id'], 'class' => 'text-center'];
                },
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'transport_found',
                'label' => 'Транспорт',
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    return ['id' => 'ht' . $model['id'], 'class' => 'text-center'];
                },
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'driver_found',
                'label' => 'Водитель',
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    return ['id' => 'hd' . $model['id'], 'class' => 'text-center'];
                },
                'headerOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<?php
$urlCalculateDuration = Url::to(['/ferrymen/get-duration-for-route']);
$this->registerJs(<<<JS
// Функция запрашивает расстояния по данным всех строк, выведенных в таблицу.
//
function calculateDuration() {
    $("tr").each(function() {
        id = $(this).attr("data-key");
        if (id != undefined && id != "") {
            $("#ht" + id).html('$preloader');
            $("#hd" + id).html('$preloader');
            $.get("$urlCalculateDuration?project_id=" + id, function(retval) {
                if (retval != false) {
                    \$blockHt = $("#ht" + retval.project_id);
                    \$blockHd = $("#hd" + retval.project_id);
                    \$blockDriver = $("#ferryman" + retval.project_id);

                    if (retval.ferryman_id != -1) \$blockDriver.html('<a href="/ferrymen/update?id=' + retval.ferryman_id + '" target="_blank" title="Открыть в новом окне" data-pjax="0">' + \$blockDriver.text() + '</a>');

                    switch (retval.transport_found) {
                        case true:
                            \$blockHt.html('$iconOk');
                            break;
                        case false:
                            \$blockHt.html('$iconCancel');
                            break;
                        case -1:
                            \$blockHt.html('$iconUndefined');
                            break;
                    }

                    switch (retval.driver_found) {
                        case true:
                            \$blockHd.html('$iconOk');
                            break;
                        case false:
                            \$blockHd.html('$iconCancel');
                            break;
                        case -1:
                            \$blockHd.html('$iconUndefined');
                            break;
                    }
                }
            });
        }
    });
} // calculateDuration()

calculateDuration();
JS
, \yii\web\View::POS_READY);
?>
