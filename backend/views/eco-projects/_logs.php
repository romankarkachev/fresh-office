<?php

use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoProjectsLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $dpStatesSummary yii\data\ArrayDataProvider */
?>
<div id="block-logs" class="eco-projects-logs collapse">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создано',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'createdByProfileName',
            'stateName',
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjectsLogs */
                    /* @var $column \yii\grid\DataColumn */

                    return Yii::$app->formatter->asNtext($model->{$column->attribute});
                },
            ],
        ],
    ]); ?>

    <?= GridView::widget([
        'dataProvider' => $dpStatesSummary,
        'layout' => '{items}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'name:ntext:Показатель',
            [
                'attribute' => 'time',
                'label' => 'Затраты по времени',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\StatesEcoProjects */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\foProjects::downcounter($model['time']);
                },
            ]
        ],
    ]); ?>

    <p class="text-muted"><em>Конец истории изменения статусов.</em></p>
    <hr />

</div>
