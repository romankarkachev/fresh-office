<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use common\models\foProjects;

/* @var $this yii\web\View */
/* @var $searchModel common\models\NotifReceiversStatesNotChangedByTimeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Получатели оповещений о проектах, статус которых не меняется заданное администратором время | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Получатели оповещений значительно просроченных проектов';
?>
<div class="notif-receivers-states-not-changed-by-time-list">
    <p>
        В справочнике храняется E-mail получателей оповещений по проектам, статусы которых не менялись заданное
        администратором критическое время.
    </p>
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'receiver',
            'stateName',
            [
                'attribute' => 'time',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\NotifReceiversStatesNotChangedByTime */
                    /* @var $column \yii\grid\DataColumn */

                    return foProjects::downcounter($model->{$column->attribute});
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>
</div>
