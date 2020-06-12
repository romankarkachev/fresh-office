<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\controllers\DesktopWidgetsController;
use common\models\DesktopWidgetsAccess;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\DesktopWidgetsAccess */
?>

<?php Pjax::begin(['id' => 'pjax-usage', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('_usage_form_pjax', ['model' => $model]); ?>

<?= backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'columns' => [
        'typeName',
        [
            'label' => '',
            'format' => 'raw',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\DesktopWidgetsAccess */
                /* @var $column \yii\grid\DataColumn */

                return $model->entityName;
            },
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'class' => 'backend\components\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    // только так не скроллится наверх (то есть при помощи заключения в форму):
                    return Html::beginForm([DesktopWidgetsController::URL_DELETE_USAGE, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            [DesktopWidgetsController::URL_DELETE_USAGE, 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено физическое удаление виджета с Рабочего стола пользователей. Продолжить?',
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
