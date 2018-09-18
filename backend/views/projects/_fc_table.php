<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $is_detailed bool признак детализированной выборки */

if ($is_detailed) {
    $columns = [
        'id',
        [
            'attribute' => 'ferrymanRep',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /** @var \common\models\Projects $model */
                /** @var \yii\grid\DataColumn $column */

                if (!empty($model->ferryman_id))
                    return Html::a($model->{$column->attribute} . ' <i class="fa fa-share-square-o"></i>', Url::to(['/ferrymen/update', 'id' => $model->ferryman_id]), ['target' => '_blank', 'data-pjax' => '0']);
                else
                    return $model->{$column->attribute};
            }
        ],
        'data',
        'address',
        'cityName',
    ];
}
else {
    $columns = [
        [
            'attribute' => 'ferrymanRep',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /** @var \common\models\Projects $model */
                /** @var \yii\grid\DataColumn $column */

                if (!empty($model->ferryman_id))
                    return Html::a($model->{$column->attribute} . ' <i class="fa fa-share-square-o"></i>', Url::to(['/ferrymen/update', 'id' => $model->ferryman_id]), ['target' => '_blank', 'data-pjax' => '0']);
                else
                    return $model->{$column->attribute};
            }
        ],
        'cityNames',
    ];
}
?>

<?php Pjax::begin(['id' => 'pjax-projects', 'enablePushState' => false, 'enableReplaceState' => false]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}{pager}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => $columns,
    'showOnEmpty' => false,
]); ?>

<?php Pjax::end(); ?>
