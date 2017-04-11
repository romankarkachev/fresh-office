<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportTurnover */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Отчет по клиентам за период | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отчет по клиентам за период';
?>
<div class="reports-turnover">
    <p class="text-muted text-justify">Примечания. Чтобы изменить условия выборки, нажмите кнопку &laquo;Отбор&raquo;. При этом выдвинется форма с полями условий. В поле &laquo;Дата&raquo; необходимо поставить ту дату, менее которой будет сделана выборка. Например, при выборе 01.01.2017 будут отобраны те контрагенты, которые первый раз заплатили 01 января 2017 г. или ранее. После выбора даты необходимо делать паузу в 2 секунды (особенность виджета). Чтобы показать все записи без разбивки на страницы, оставьте поле &laquo;Записей&raquo; пустым.</p>
    <p class="text-muted text-justify">Чтобы сортировать, необходимо щелкнуть по заголовку столбца.</p>
    <p class="text-muted text-justify">Обратите внимание, что выгрузка в Excel производится с теми же параметрами отбора и с той сортировкой, которая применяется.</p>
    <?= $this->render('_search_turnover', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', '/reports/turnover?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{summary}{pager}',
        'tableOptions' => [
            'class' => 'table table-striped table-hover',
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'locale' => 'ru_RU',
            'defaultTimeZone' => 'UTC',
            'currencyCode' => 'RUR',
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i:s',
            'timeFormat' => 'php:H:i:s',
            'thousandSeparator' => ' ',
            'decimalSeparator' => ',',
            'numberFormatterOptions' => [
                NumberFormatter::MIN_FRACTION_DIGITS => 0,
                NumberFormatter::MAX_FRACTION_DIGITS => 2,
            ],
            'nullDisplay' => '',
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
            [
                'attribute' => 'first_payment',
                'format' =>  ['date', 'php:d.m.Y'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '110'],
            ],
            [
                'attribute' => 'turnover',
                'format' => ['decimal', 'decimals' => 2],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center text-bold'],
                'options' => ['width' => '110'],
            ],
        ],
    ]); ?>

</div>
