<?php

use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjectsMilestones */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?= $form->field($model, 'date_close_plan')->widget(DateControl::className(), [
    'value' => $model->date_close_plan,
    'type' => DateControl::FORMAT_DATE,
    'displayFormat' => 'php:d.m.Y',
    'saveFormat' => 'php:Y-m-d',
    'widgetOptions' => [
        'layout' => '{input}{picker}',
        'options' => ['placeholder' => '- выберите дату -'],
        'pluginOptions' => [
            'weekStart' => 1,
            'autoclose' => true,
        ],
        'pluginEvents' => [
            'changeDate' => 'function(e) { dateClosePlanOnChange(e, ' . $model->id . ', e.format("yyyy-mm-dd")); }',
        ],
    ],
]) ?>
