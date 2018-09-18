<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportCaDuplicates */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Отчет по дубликатам в контрагентах | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отчет по дубликатам в контрагентах';
?>
<div class="reports-caduplicates">
    <?= $this->render('_search_caduplicates', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', '/reports/ca-duplicates?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{summary}{pager}',
        'tableOptions' => [
            'class' => 'table table-striped table-hover',
        ],
        'columns' => [
            [
                'attribute' => 'name',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '120'],
            ],
            'parameter',
            'owners',
            [
                'header' => 'Действия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\ReportCaDuplicates */
                    /* @var $column \yii\grid\DataColumn */

                    return Html::a('<i class="fa fa-copy"></i> Объединить', Url::to([
                        '/process/merge-customers', 'field' => $model['field'], 'criteria' => trim($model['parameter'])
                    ]), [
                        'class' => 'btn btn-xs btn-info',
                        'title' => 'Объединить карточки контрагентов',
                    ]);
                },
            ],
        ],
    ]); ?>

</div>
