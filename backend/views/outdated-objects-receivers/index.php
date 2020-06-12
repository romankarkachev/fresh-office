<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\OutdatedObjectsReceiversController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\OutdatedObjectsReceiversSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Получатели уведомлений | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = OutdatedObjectsReceiversController::ROOT_LABEL;
?>
<div class="outdated-objects-receivers-list">
    <p>
        В справочнике хранятся E-mail получателей оповещений о проектах или договорах экологии, запросах транспорта,
        пакетах корреспонденции, находящихся без движения приличное время.
    </p>
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'sectionName',
            'receiver',
            [
                'attribute' => 'time',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\OutdatedObjectsReceivers */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\foProjects::downcounter($model->{$column->attribute});
                },
            ],
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
