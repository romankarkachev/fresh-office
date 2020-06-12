<?php

use backend\controllers\EcoContractsController;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\EcoMcTp */
?>

<?php Pjax::begin(['id' => 'pjax-reports', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('_report_form_pjax', ['model' => $model]); ?>

<?= \backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'columns' => [
        'reportName',
        [
            'attribute' => 'date_deadline',
            'label' => 'Крайний срок',
            'format' => 'date',
            'options' => ['width' => '130'],
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'attribute' => 'date_fact',
            'label' => 'Дата подачи',
            'format' => 'date',
            'options' => ['width' => '130'],
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'class' => 'backend\components\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    // только так не скроллится наверх (то есть при помощи заключения в форму):
                    return Html::beginForm([EcoContractsController::URL_DELETE_REPORT, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            [EcoContractsController::URL_DELETE_REPORT, 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено физическое удаление отчета из данного договора. Операция необратима. Продолжить?',
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
