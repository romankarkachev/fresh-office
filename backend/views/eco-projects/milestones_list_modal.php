<?php

use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $model \common\models\EcoProjects */
/* @var $dataProvider yii\data\ActiveDataProvider */

$currentMilestone = null;
?>
<div class="projects-milestones-list-in-modal">
    <?= $this->render('_project_details', ['model' => $model]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'class' => 'table table-condensed table-striped',
        'rowOptions' => function ($model, $key, $index, $grid) use (&$currentMilestone) {
            /* @var $model \common\models\EcoProjectsMilestones */

            $options = [];

            if (!$model->is_affects_to_cycle_time) {
                $options['class'] = 'text-muted';
            }

            if (empty($model->closed_at) && (empty($currentMilestone))) {
                $options['class'] = 'text-bold info';
                $currentMilestone = $model->id;
            }

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
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    // этапы, для закрытиях которых требуется загрузка минимум одного файла, пометим звездочкой
                    $result = $model[$column->attribute] . ($model->is_file_reqiured ? ' <strong>*</strong>' : '') . ' <small><em class="text-normal text-muted">' . \common\models\foProjects::declension($model->time_to_complete_required, ['день','дня','дней']) . '</em></small>';

                    return $result;
                },
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
                'label' => 'Срок',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjects */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\EcoProjects::milestonesListTerminColumn($model, $key, $index, $column);
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
                'label' => 'Состояние',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) use (&$currentMilestone) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty($model->closed_at))
                        return '<i class="fa fa-check-circle text-success" aria-hidden="true" title="Этап закрыт"></i>';
                    elseif (empty($model->closed_at) && (empty($currentMilestone)))
                        return '<i class="fa fa-circle-o-notch fa-spin text-muted" title="Этап еще не выполнен"></i>';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <p class="text-justify">
        Звездочкой помечены этапы, для закрытия которых обязательно необходимо преодставить минимум один файл. Полностью
        серая строка не принимает участия в расчетах сроков завершения проекта.
    </p>
</div>
