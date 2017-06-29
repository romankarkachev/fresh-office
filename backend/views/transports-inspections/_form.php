<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use common\models\TechnicalConditions;

/* @var $this yii\web\View */
/* @var $model common\models\TransportInspections */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="transport-inspections-form">
    <?php $form = ActiveForm::begin([
        'action' => ['create-inspection'],
    ]); ?>

    <?= $form->field($model, 'transport_id')->hiddenInput()->label(false) ?>

    <div class="panel panel-success">
        <div class="panel-heading">Форма нового объекта</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'inspected_at')->widget(DateControl::className(), [
                        'value' => $model->inspected_at,
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
                <div class="col-md-2">
                    <?= $form->field($model, 'tc_id')->widget(Select2::className(), [
                        'data' => TechnicalConditions::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
            <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание']) ?>

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
