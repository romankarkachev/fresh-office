<?php

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoProjectsLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="block-states-summary" class="eco-projects-logs collapse">
    <?= \backend\components\grid\GridView::widget([
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
            ]
        ],
    ]); ?>

</div>
