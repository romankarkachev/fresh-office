<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\DriversInstructings */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="drivers-instructings-form">
    <?php $form = ActiveForm::begin([
        'action' => ['create-instructing'],
    ]); ?>

    <?= $form->field($model, 'driver_id')->hiddenInput()->label(false) ?>

    <div class="panel panel-success">
        <div class="panel-heading">Форма нового объекта</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'instructed_at')->widget(DateControl::className(), [
                        'value' => $model->instructed_at,
                        'type' => DateControl::FORMAT_DATE,
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'выберите'],
                            'layout' => '{input}{picker}',
                            'pluginOptions' => [
                                'weekStart' => 1,
                                'autoclose' => true
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'place')->textInput(['maxlength' => true, 'placeholder' => 'Введите место']) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'responsible')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя ответственного']) ?>

                </div>
            </div>
            <div class="form-group">
                <?php if ($model->isNewRecord): ?>
                    <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

                <?php else: ?>
                    <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
