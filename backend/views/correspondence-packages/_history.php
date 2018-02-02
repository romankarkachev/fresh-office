<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CorrespondencePackagesHistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="block-history" class="correspondence-package-history collapse">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
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
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackagesHistorySearch */
                    /* @var $column \yii\grid\DataColumn */

                    return nl2br($model->{$column->attribute});
                },
            ]
        ],
    ]); ?>
</div>
