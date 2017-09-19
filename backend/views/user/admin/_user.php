<?php

use kartik\select2\Select2;
use common\models\AuthItem;

/* @var yii\widgets\ActiveForm $form */
/* @var \common\models\User $user */
?>

<?= $form->field($user, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя']) ?>

<?= $form->field($user, 'username')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'email')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'role_id')->widget(Select2::className(), [
    'data' => AuthItem::arrayMapForSelect2(),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите роль -'],
    'hideSearch' => true,
]); ?>

<?= $form->field($user, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>

<?= $form->field($user, 'password_confirm')->passwordInput(['placeholder' => 'Подтвердите пароль']) ?>
