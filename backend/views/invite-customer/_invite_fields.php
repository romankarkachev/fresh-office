<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model backend\models\CustomerInvitationForm */
/* @var $emails array массив E-mail адресов заказчика */
/* @var $form yii\bootstrap\ActiveForm */
?>

            <?= $form->field($model, 'email')->widget(Select2::className(), [
                'data' => $emails,
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>
