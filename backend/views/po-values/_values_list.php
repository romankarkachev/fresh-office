<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\controllers\PoPropertiesController;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\PoValues */
?>

<?php Pjax::begin(['id' => 'pjax-values', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('/po-values/_form_pjax', ['model' => $model]); ?>

<?= \backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\PoValues */

                return Html::input(
                    'text',
                    'renameValue' . $model->id,
                    $model->name,
                    [
                        'id' => 'fieldRenameValue' . $model->id,
                        'data-id' => $model->id,
                        'class' => 'form-control collapse',
                    ]) .
                    Html::a(
                        $model->{$column->attribute},
                        '#',
                        [
                            'id' => 'renameValue' . $model->id,
                            'data-id' => $model->id,
                            'class' => 'link-ajax',
                            'title' => 'Нажмите, чтобы переименовать',
                        ]);
            },
        ],
        [
            'class' => 'backend\components\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    // только так не скроллится наверх (то есть при помощи заключения в форму):
                    return Html::beginForm([PoPropertiesController::URL_DELETE_VALUE, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            [PoPropertiesController::URL_DELETE_VALUE, 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено физическое удаление значения из базы. Операция необратима. Продолжить?',
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]
                        ) . Html::endForm();
                }
            ],
            'options' => ['width' => '20'],
        ],
    ],
]); ?>

<?php Pjax::end(); ?>
