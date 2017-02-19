<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Products */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'is_deleted')->textInput() ?>

    <?= $form->field($model, 'author_id')->textInput() ?>

    <?= $form->field($model, 'type')->textInput() ?>

    <?= $form->field($model, 'unit')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uw')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dc')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fkko')->textInput() ?>

    <?= $form->field($model, 'fkko_date')->textInput() ?>

    <?= $form->field($model, 'fo_id')->textInput() ?>

    <?= $form->field($model, 'fo_name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fo_fkko')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'author_id')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
