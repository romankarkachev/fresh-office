<?php

use backend\controllers\TendersController;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\TendersTp */
?>

<?php Pjax::begin(['id' => 'pjax-waste', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('_waste_form_pjax', ['model' => $model]); ?>

<?= \backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'columns' => [
        'fkko_name',
        [
            'class' => 'backend\components\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    // только так не скроллится наверх (то есть при помощи заключения в форму):
                    return Html::beginForm([TendersController::URL_DELETE_WASTE, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            [TendersController::URL_DELETE_WASTE, 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено физическое удаление вида отходов из данного тендера. Операция необратима. Продолжить?',
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
