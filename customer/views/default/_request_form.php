<?php

use common\components\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymanOrderForm */
/* @var $form common\components\bootstrap\ActiveForm */
?>

<div class="request-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmSendRequest',
        'action' => ['default/send-request'],
    ]); ?>

    <?= $form->field($model, 'comment')->textarea(['autofocus' => true, 'rows' => 6, 'placeholder' => 'Введите текст своего комментария']) ?>

    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
