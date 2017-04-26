<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportNoTransportHasProjects */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Отчет по клиентам с утилизацией без транспорта за период | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отчет по клиентам с утилизацией без транспорта за период';
?>
<div class="reports-notransporthasprojects">
    <p class="text-muted text-justify">Примечания. Чтобы изменить условия выборки, нажмите кнопку &laquo;Отбор&raquo;. При этом выдвинется форма с полями условий. После выбора даты необходимо делать паузу в 2 секунды (особенность виджета). Чтобы показать все записи без разбивки на страницы, оставьте поле &laquo;Записей&raquo; пустым.</p>
    <p class="text-muted text-justify">Чтобы сортировать, необходимо щелкнуть по заголовку столбца.</p>
    <?= $this->render('_search_notransporthasprojects', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', '/reports/no-transport-has-projects?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{summary}{pager}',
        'tableOptions' => [
            'class' => 'table table-striped table-hover',
        ],
        'columns' => [
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
