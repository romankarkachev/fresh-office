<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\EdfEmailFilesForm;

/* @var $this yii\web\View */
/* @var $model common\models\EdfEmailFilesForm */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="transport-requests-dialogs-form">
    <?php $form = ActiveForm::begin([
        'action' => \backend\controllers\EdfController::URL_EMAIL_SELECTED_FILES_AS_ARRAY,
        'options' => ['id' => 'frmEmailFiles'],
    ]); ?>

    <?= $form->field($model, 'files')->textInput(['disabled' => true]) ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'email_receiver', ['template' => '<div class="input-group"><span class="input-group-addon">@</span>{input}</div>{error}'])->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail получателя']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'email_sender', ['template' => '<div class="input-group"><span class="input-group-addon">@</span>{input}</div>{error}'])->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail отправителя']) ?>

        </div>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий, который будет включен в текст письма'])->label('Добавить в письмо:') ?>

    <?= $form->field($model, 'ed_id')->hiddenInput()->label(false) ?>

    <?= Html::submitButton('<i class="fa fa-plane" aria-hidden="true"></i> Отправить файлы', ['class' => 'btn btn-info btn-block', 'id' => 'btnSubmitEmailFilesForm']) ?>

    <?php ActiveForm::end(); ?>

</div>
