<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\components\grid\GridView;
use backend\controllers\EcoTypesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoTypesMilestonesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\EcoTypesMilestones */
/* @var $action string */
?>
<div class="types-milestones-list">
    <?php Pjax::begin(['id' => 'pjax-milestones' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= $this->render('_types_milestone_form', [
        'dataProvider' => $dataProvider,
        'model' => $model,
        'action' => $action,
    ]); ?>

    <?= GridView::widget([
        'id' => 'gw-milestones',
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'columns' => [
            [
                'attribute' => 'order_no',
                'options' => ['width' => '40'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'milestoneName',
            [
                'attribute' => 'is_file_reqiured',
                'label' => 'Файл',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoTypesMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return ($model->{$column->attribute} ? '<i class="fa fa-check-circle text-success" aria-hidden="true" title="Предоставление минимум одного файла обязательно для закрытия этого этапа"></i>' : '');
                },
                'options' => ['width' => '30'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'is_affects_to_cycle_time',
                'label' => 'Учитывается при подсчете',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoTypesMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return ($model->{$column->attribute} ? '<i class="fa fa-check-circle text-success" aria-hidden="true" title="Время выполнения, указанное для данного этапа, принимается во внимание при подсчете сроков завершения проекта"></i>' : '');
                },
                'options' => ['width' => '30'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'time_to_complete_required',
                'label' => 'Требуется времени',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoTypesMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\foProjects::declension($model->{$column->attribute}, ['день','дня','дней']);
                },
                'options' => ['width' => '90'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', [EcoTypesController::URL_DELETE_MILESTONE, 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => true]);
                    }
                ],
                'options' => ['width' => '20'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
