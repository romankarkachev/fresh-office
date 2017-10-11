<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\LicensesRequests */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="licenses-requests-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'state_id')->textInput() ?>

    <?= $form->field($model, 'ca_email')->textInput() ?>

    <?= $form->field($model, 'ca_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ca_id')->textInput() ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
