<?php

use kartik\datecontrol\DateControl;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Tasks */
?>
<?php $form = ActiveForm::begin(['id' => 'frmPostpone']); ?>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'start_at')->widget(DateControl::class, [
            'value' => $model->start_at,
            'type' => DateControl::FORMAT_DATETIME,
            'displayFormat' => 'php:d.m.Y H:i',
            'saveFormat' => 'php:U',
            'widgetOptions' => [
                'layout' => '{input}{picker}',
                'options' => [
                    'placeholder' => '- выберите дату и время -',
                    'autocomplete' => 'off',
                ],
                'pluginOptions' => [
                    'weekStart' => 1,
                    'autoclose' => true,
                ],
            ],
        ])->label('Новая дата') ?>

    </div>
</div>
<?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

<?= \yii\helpers\Html::button('Перенести', ['id' => 'btnPostpone', 'class' => 'btn btn-primary', 'title' => 'Перенести задачу на выбранные дату и время']) ?>

<?php ActiveForm::end(); ?>
