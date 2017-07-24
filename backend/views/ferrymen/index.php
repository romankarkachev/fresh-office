<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FerrymenSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Перевозчики | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Перевозчики';
?>
<div class="ferrymen-list">
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
            'name',
            'ftName',
            'pcName',
            'stateName',
            [
                'attribute' => 'phone',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Ferrymen */
                    return \common\models\Ferrymen::normalizePhone($model->{$column->attribute});
                }
            ],
            'email:email',
            'contact_person',
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
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
