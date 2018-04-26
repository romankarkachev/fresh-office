<?php

use yii\widgets\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>

<?php Pjax::begin(['id' => 'pjax-projects', 'enablePushState' => false, 'enableReplaceState' => false]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}{pager}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        'id',
        'ferrymanRep',
        'data',
        'address',
        'cityName',
    ],
]); ?>

<?php Pjax::end(); ?>
