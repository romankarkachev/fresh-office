<?php

use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?= $form->field($model, 'fo_project_id')->widget(MaskedInput::class, [
    'mask' => '99999',
    'clientOptions' => ['placeholder' => ''],
])->textInput(['maxlength' => true, 'placeholder' => 'ID проекта', 'title' => 'Введите ID проекта из CRM Fresh Office']) ?>
