<?php

use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportEmptycustomers */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Отчет по пустым клиентам (без контактов, оборотов и проектов) | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отчет по пустым клиентам';
?>
<div class="reports-emptycustomers">
    <p class="text-muted text-justify">Примечание. Чтобы показать все записи без разбивки на страницы, оставьте поле &laquo;Записей&raquo; пустым. Отметьте галочками контрагентов, которые хотите удалить. Нажмите &laquo;Выполнить обработку&raquo;. Если обработка выполнится успешно, кнопка станет зеленой. В случае ошибки - красной.</p>
    <div class="row">
        <div class="col-md-6">
            <?= $this->render('_search_emptycustomers', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

        </div>
        <div class="col-md-6">
            <?= $this->render('_process_emptycustomers'); ?>

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
$url_process = Url::to(['/reports/emptycustomers']);
$this->registerJs(<<<JS
// Функция-обработчик щелчка по кнопке Выполнить обработку.
//
function ProcessResponsibleOnClick() {
    if (confirm('Вы действительно хотите отправить в корзину выбранных контрагентов?')) {
        var ids = $("#gw-ca-nf").yiiGridView("getSelectedRows");
        var \$btn = $(this);

        // делаем кнопку обычной
        \$btn.removeClass().addClass("btn btn-default");

        // включаем индикацию на кнопке (preloader)
        \$btn.button("loading");

        $.post("$url_process", {ca_ids: ids}, function( data, status ) {
            \$btn.button("reset");
            if (data == false || status == "error")
                \$btn.removeClass().addClass("btn btn-danger");
            else
                \$btn.removeClass().addClass("btn btn-success");
        });
    }

    return false;
} // ProcessResponsibleOnClick()

$(document).on("click", "#btn-execute-process", ProcessResponsibleOnClick);
JS
, \yii\web\View::POS_READY);
?>