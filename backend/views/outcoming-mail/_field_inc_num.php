<?php

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMail */
/* @var $form yii\bootstrap\ActiveForm */
?>
<div class="col-md-3">
    <?= $form->field($model, 'inc_num')->textInput(['maxlength' => true, 'placeholder' => 'Введите номер'])->label('Исх. №') ?>

</div>
