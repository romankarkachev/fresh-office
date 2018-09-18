<?php

use yii\helpers\Url;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportNofinances */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Отчет по клиентам без оборотов (с возможностью изменить ответственного менеджера у выбранных) | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отчет по клиентам без оборотов';
?>
<div class="reports-nofinances">
    <p class="text-muted text-justify">Примечание. Чтобы показать все записи без разбивки на страницы, оставьте поле &laquo;Записей&raquo; пустым. Отметьте галочками контрагентов, которые хотите передать. Затем выберите нового ответственного и нажмите &laquo;Выполнить обработку&raquo;. Если обработка выполнится успешно, кнопка станет зеленой. В случае ошибки - красной.</p>
    <div class="row">
        <div class="col-md-6">
            <?= $this->render('_search_nofinances', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

        </div>
        <div class="col-md-6">
            <?= $this->render('_process_nofinances'); ?>

        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'gw-ca-nf',
        'layout' => '{items}{summary}{pager}',
        'tableOptions' => [
            'class' => 'table table-striped table-hover',
        ],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'options' => ['width' => '30'],
            ],
            [
                'attribute' => 'id',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '60'],
            ],
            'name',
            [
                'attribute' => 'responsible',
                'options' => ['width' => '200'],
            ],
        ],
    ]); ?>

</div>
<?php
$url_process = Url::to(['/reports/nofinances']);
$this->registerJs(<<<JS
// Функция-обработчик щелчка по кнопке Выполнить обработку.
//
function ProcessResponsibleOnClick() {
    var ids = $("#gw-ca-nf").yiiGridView("getSelectedRows");
    var \$btn = $(this);

    // делаем кнопку обычной
    \$btn.removeClass().addClass("btn btn-default");

    // включаем индикацию на кнопке (preloader)
    \$btn.button("loading");

    $.post("$url_process", {ca_ids: ids, manager_id: $("#process-responsible").val()}, function( data, status ) {
        \$btn.button("reset");
        if (data == false || status == "error")
            \$btn.removeClass().addClass("btn btn-danger");
        else
            \$btn.removeClass().addClass("btn btn-success");
    });

    return false;
} // ProcessResponsibleOnClick()

$(document).on("click", "#btn-execute-process", ProcessResponsibleOnClick);
JS
, \yii\web\View::POS_READY);
?>
