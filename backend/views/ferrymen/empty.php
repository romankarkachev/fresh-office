<?php

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider of common\models\Ferrymen[] */

$this->title = 'Перевозчики, по которым проводилась оплата, без транспорта и/или водителей | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = 'Пустые';
?>
<p>
    В списке перевозчики, по которым есть хотя бы один платежный ордер в любом статусе, но нет ни одного транспортного
    средства и/или водителя.
</p>
<?= backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-striped table-hover table-bordered'],
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\Ferrymen */
                /* @var $column \yii\grid\DataColumn */

                return Html::a($model->{$column->attribute}, ['/ferrymen/update', 'id' => $model->id], ['title' => 'Открыть в новом окне', 'target' => '_blank']);
            },
        ],
        [
            'attribute' => 'driversCount',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\Ferrymen */
                /* @var $column \yii\grid\DataColumn */

                return !empty($model->{$column->attribute}) ? $model->{$column->attribute} : '';
            },
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '90'],
        ],
        [
            'attribute' => 'transportCount',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\Ferrymen */
                /* @var $column \yii\grid\DataColumn */

                return !empty($model->{$column->attribute}) ? $model->{$column->attribute} : '';
            },
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '90'],
        ],
    ],
]); ?>
