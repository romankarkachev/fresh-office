<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\FerrymanInvitationForm */
/* @var $invitationLast \common\models\FerrymenInvitations */
/* @var $form yii\bootstrap\ActiveForm */

$emailTemplate = '<div class="input-group">
    <span class="input-group-addon">@</span>
    {input}
    <span id="spanButtonSubmit" class="input-group-btn">
        <button id="btnSendInvitation" class="btn btn-default" type="submit">Отправить</button>
    </span>
</div>{error}';
?>

<div class="invite-ferryman-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmInviteFerryman',
        'action' => '/ferrymen/send-invitation',
        'enableAjaxValidation' => true,
        'validationUrl' => ['/ferrymen/validate-invitation'],
    ]); ?>

    <?= $form->field($model, 'email', ['template' => $emailTemplate])->textInput(['type' => 'email', 'placeholder' => 'Введите E-mail получателя']) ?>

    <?php if ($invitationLast != null): ?>
    <p class="text-muted"><em>Приглашения уже отправлялись ранее (последнее &mdash; <?= Yii::$app->formatter->asDate($invitationLast->created_at, 'php:d F Y г. в H:i') ?>).</em></p>
    <?php endif; ?>
    <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
