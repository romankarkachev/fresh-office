<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use common\models\foProjects;
use common\models\NotifReceiversStatesNotChangedByTime;

/* @var $this yii\web\View */
/* @var $searchModel common\models\NotifReceiversStatesNotChangedByTimeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statesCp array статусы пакетов корреспонденции */
/* @var $statesProjects array статусы проектов */

$this->title = 'Получатели оповещений о проектах и пакетах корреспонденции, статус которых не меняется заданное администратором время | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Получатели оповещений о значительно просроченных проектах и пакетах';
?>
<div class="notif-receivers-states-not-changed-by-time-list">
    <p>
        В справочнике хранятся E-mail получателей оповещений о проектах и пакетах корреспонденции, статусы которых не
        менялись заданное администратором критическое время.
    </p>
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'label' => 'Тип',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\NotifReceiversStatesNotChangedByTime */
                    /* @var $column \yii\grid\DataColumn */

                    switch ($model->section) {
                        case NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ:
                            return '<i class="fa fa-book text-success" aria-hidden="true" title="Применяется для проектов"></i>';
                        case NotifReceiversStatesNotChangedByTime::SECTION_ПАКЕТЫ:
                            return '<i class="fa fa-envelope text-info" aria-hidden="true" title="Применяется для пакетов корреспонденции"></i>';
                        default:
                            return '';
                    }
                },
                'options' => ['width' => '30'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'receiver',
            [
                'attribute' => 'stateNameManual',
                'value' => function($model, $key, $index, $column) use ($statesProjects, $statesCp) {
                    /* @var $model \common\models\NotifReceiversStatesNotChangedByTime */
                    /* @var $column \yii\grid\DataColumn */

                    switch ($model->section) {
                        case NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ:
                            return $model->getStateNameManual($statesProjects);
                        case NotifReceiversStatesNotChangedByTime::SECTION_ПАКЕТЫ:
                            return $model->getStateNameManual($statesCp);
                        default:
                            return '';
                    }
                },
            ],
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
