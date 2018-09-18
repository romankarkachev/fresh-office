<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Номенклатура | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Номенклатура';
?>
<div class="products-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-search"></i> Поиск', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Импорт из Excel', ['import'], ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '<div class="table-responsive">{items}</div>{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'name',
                'options' => ['width' => '30%'],
                'value' => function ($model) {
                    return StringHelper::truncate($model->name, 80, '...');
                },
            ],
            [
                'attribute' => 'fo_id',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'unit',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'uw',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'dc',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'fkko',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
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
