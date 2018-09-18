<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CEMessages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cemessages-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'detected_at')->textInput() ?>

    <?= $form->field($model, 'obtained_at')->textInput() ?>

    <?= $form->field($model, 'mailbox_id')->textInput() ?>

    <?= $form->field($model, 'uid')->textInput() ?>

    <?= $form->field($model, 'subject')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'body_text')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'body_html')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'attachment_count')->textInput() ?>

    <?= $form->field($model, 'header')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'is_complete')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
