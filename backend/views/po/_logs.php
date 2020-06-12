<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PoStatesHistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="block-logs" class="po-states-history collapse">
    <?php Pjax::begin(['id' => 'pjax-logs', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

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
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\PoStatesHistory */
                    /* @var $column \yii\grid\DataColumn */

                    return nl2br($model->{$column->attribute});
                },
            ]
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
