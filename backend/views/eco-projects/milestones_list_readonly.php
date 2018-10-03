<?php

use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $model \common\models\EcoProjects */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $form yii\bootstrap\ActiveForm */
?>
<div class="projects-milestones-list">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'rowOptions' => function ($model, $key, $index, $grid) {
            $options = [];

            if (!$model['is_affects_to_cycle_time']) {
                $options['class'] = 'text-muted';
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
                    $result = $model[$column->attribute];
                    if ($model['is_file_reqiured']) $result .= ' <i class="fa fa-floppy-o text-primary" aria-hidden="true" title="Для закрытия этапа обязательно необходимо преодставить минимум один файл"></i>';
                    return $result;
                },
                'contentOptions' => function ($model, $key, $index, $grid) {
                    $options = [];

                    if (!$model['is_affects_to_cycle_time']) {
                        $options['class'] = 'text-muted';
                    }

                    return $options;
                },
            ],
            [
                'attribute' => 'time_to_complete_required',
                'label' => 'Требуется времени',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\foProjects::declension($model[$column->attribute], ['день','дня','дней']);
                },
                'options' => ['width' => '90'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'date_close_plan',
                'label' => 'Планируемая дата завершения',
            ],
        ],
    ]); ?>

    <em class="text-muted">Серым цветом выделяются этапы, которые не участвуют в расчете планируемой даты завершения проекта.</em>
    <p>Планируемая дата завершения проекта: <strong><?= Yii::$app->formatter->asDate($model->date_close_plan, 'php:d F Y г.') ?></strong></p>

    <?= $form->field($model, 'date_close_plan')->hiddenInput()->label(false) ?>

</div>
