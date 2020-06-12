<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $action string */
?>
<div class="fields-list">
    <?php Pjax::begin(['id' => 'pjax-fields' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= $this->render('_field_form', [
        'dataProvider' => $dataProvider,
        'model' => $model,
        'action' => $action,
    ]); ?>

    <?= \backend\components\grid\GridView::widget([
        'id' => 'gwFields',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '35'],
            ],
            'name',
            'description',
            [
                'attribute' => 'widgetName',
                'label' => 'Виджет',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '200'],
            ],
            [
                'attribute' => 'alias',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', [TenderFormsController::URL_DELETE_KIND_FIELD, 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => true]);
                    }
                ],
                'options' => ['width' => '20'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
