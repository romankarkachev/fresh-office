<?php

use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\ExportWasteReminderForm */
/* @var $form yii\bootstrap\ActiveForm */

$contactPersonName = $model->project->contactPersonName;
?>

<div class="assign-ferryman-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmExportWasteReminder',
        'action' => '/projects/send-export-waste-reminder',
        'enableAjaxValidation' => false,
    ]); ?>

    <p><strong>Контрагент:</strong> <?= $model->project->companyName ?><?= !empty($contactPersonName) ? ', <strong>контактное лицо:</strong> ' . $contactPersonName : '' ?></p>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'email')->textInput(['placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'date')->widget(DateControl::class, [
                'value' => $model->date,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -', 'autocomplete' => 'off'],
                    'layout' => '{input}{picker}',
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'transport_info')->textarea(['placeholder' => 'Введите информацию о транспорте', 'rows' => 3]) ?>

    <?= $form->field($model, 'driver_info')->textarea(['placeholder' => 'Введите информацию о водителе', 'rows' => 3]) ?>

    <div class="form-group">
        <p>Выбирайте только файл с ТТН и файл с АПП.</p>
        <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
