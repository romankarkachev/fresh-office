<?php

use backend\components\grid\GridView;
use common\models\EcoProjects;
use common\models\foProjects;

/* @var $this yii\web\View */
/* @var $model \common\models\EcoProjects */
/* @var $dataProvider yii\data\ActiveDataProvider */

$currentMilestone = null;
$prevMilestoneClosedAt = strtotime($model->date_start . ' 00:00:00');
?>
<div class="projects-milestones-list-in-modal">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'class' => 'table table-striped',
        'rowOptions' => function ($model, $key, $index, $grid) use (&$currentMilestone, &$prevMilestoneClosedAt) {
            /* @var $model \common\models\EcoProjectsMilestones */

            $options = [];

            if (!$model->is_affects_to_cycle_time) {
                $options['class'] = 'text-muted';
            }

            if (empty($model->closed_at) && (empty($currentMilestone))) {
                $options['class'] = 'text-bold info';
                $currentMilestone = $model->id;
            }

            $prevMilestoneClosedAt = strtotime(Yii::$app->formatter->asDate($model->closed_at, 'php:Y-m-d 00:00:00'));

            return $options;
        },
        'columns' => [
            [
                'attribute' => 'order_no',
                'label' => '№',
                'options' => ['width' => '40'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'milestoneName',
                'format' => 'raw',
                'contentOptions' => function ($model, $key, $index, $grid) {
                    /* @var $model \common\models\EcoProjectsMilestones */

                    $options = [];

                    if (!$model->is_affects_to_cycle_time) {
                        $options['class'] = 'text-muted';
                    }

                    return $options;
                },
            ],
            [
                'attribute' => 'time_to_complete_required',
                'label' => 'Требуется времени',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) use (&$prevMilestoneClosedAt) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return EcoProjects::milestonesListTimeRequired($prevMilestoneClosedAt, $model, $key, $index, $column);
                },
                //'options' => ['width' => '90'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'Срок',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return EcoProjects::milestonesListTerminColumn($model, $key, $index, $column);
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => function ($model, $key, $index, $grid) {
                    /* @var $model \common\models\EcoProjectsMilestones */

                    $options = [
                        'class' => ['text-center'],
                    ];

                    if (!empty($model->closed_at) && ($model->date_close_plan == Yii::$app->formatter->asDate($model->closed_at, 'php:Y-m-d'))) {
                        $options[] = ['text-success'];
                    }

                    return $options;
                },
            ],
            [
                'label' => 'Файлы',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) use (&$currentMilestone) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return EcoProjects::milestonesListFilesColumn($currentMilestone, $model, null, $key, $index, $column);
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'Состояние',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) use (&$currentMilestone) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return EcoProjects::milestonesListToolColumn($currentMilestone, $model, $key, $index, $column);
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <p class="text-justify">
        Полностью серая строка не принимает участия в расчетах сроков завершения проекта.
    </p>
</div>
