<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<?php Pjax::begin(['id' => 'pjax-fkko', 'enablePushState' => false]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}{pager}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'fkkoRep',
    ],
]); ?>

<?php Pjax::end(); ?>
