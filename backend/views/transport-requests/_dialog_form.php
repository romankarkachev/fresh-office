<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequestsDialogs */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $action string */
?>

<div class="transport-requests-dialogs-form">
    <?php $form = ActiveForm::begin([
        'action' => ['/transport-requests/' . $action],
        'options' => ['data-pjax' => true],
    ]); ?>

    <?=  $form->field($model, 'tr_id')->hiddenInput()->label(false) ?>

    <?=  $form->field($model, 'created_by')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'message')->textarea(['autofocus' => true, 'placeholder' => 'Введите текст сообщения']) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
