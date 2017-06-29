<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $searchApplied bool */

$this->title = 'Проекты | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Проекты';
?>
<div class="projects-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'id',
            [
                'attribute' => 'type_name',
                //'options' => ['width' => '90'],
                //'headerOptions' => ['class' => 'text-center'],
                //'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'state_name',
                //'options' => ['width' => '90'],
                //'headerOptions' => ['class' => 'text-center'],
                //'contentOptions' => ['class' => 'text-center'],
            ],
            'date_start:datetime',
            'date_end:datetime',
            'ca_name',
            'manager_name',
            [
                'attribute' => 'amount',
                'format' => ['decimal', 'decimals' => 2],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'cost',
                'format' => ['decimal', 'decimals' => 2],
                'options' => ['width' => '60'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
