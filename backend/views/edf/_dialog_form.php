<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\EdfDialogs */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $action string */
?>

<div class="edf-dialogs-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmEdfNewDialogMessage',
        'action' => ['/edf/' . $action],
        'options' => ['data-pjax' => true],
    ]); ?>

    <?=  $form->field($model, 'ed_id')->hiddenInput()->label(false) ?>

    <?=  $form->field($model, 'created_by')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'message')->textarea(['placeholder' => 'Введите текст сообщения']) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
