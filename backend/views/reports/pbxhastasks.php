<?php

use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportPbxAnalytics */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Наличие задач и незавершенных проектов у контрагентов | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Наличие задач и незавершенных проектов';
?>
<div class="reports-pbxhastasks">
    <?= $this->render('_search_pbxhastasks', ['model' => $searchModel, 'searchApplied' => $searchApplied, 'queryString' => $queryString]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '35'],
            ],
            [
                'attribute' => 'pbxht_id',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '90'],
            ],
            'pbxht_name',
            'pbxht_managerName',
            [
                'attribute' => 'pbxht_projectsInProgressCount',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\ReportPbxAnalytics */
                    /* @var $column \yii\grid\DataColumn */

                    return $model[$column->attribute] > 0 ? '<i class="fa fa-check-circle text-success" aria-hidden="true" title="' . $model[$column->attribute] . '"></i>' : '';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '90'],
            ],
            [
                'attribute' => 'pbxht_tasksCount',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\ReportPbxAnalytics */
                    /* @var $column \yii\grid\DataColumn */

                    return $model[$column->attribute] > 0 ? '<i class="fa fa-check-circle text-success" aria-hidden="true" title="' . $model[$column->attribute] . '"></i>' : '';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '90'],
            ],
        ],
    ]); ?>

</div>
