<?php

use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\AuthItem;
use common\models\Offices;

/* @var yii\widgets\ActiveForm $form */
/* @var \common\models\User $user */
?>

<?= $form->field($user, 'surname')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите фамилию']) ?>

<?= $form->field($user, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя']) ?>

<?= $form->field($user, 'patronymic')->textInput(['maxlength' => true, 'placeholder' => 'Введите отчество']) ?>

<?= $form->field($user, 'username')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'email')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны']) ?>

<?= $form->field($user, 'role_id')->widget(Select2::className(), [
    'data' => ArrayHelper::map(AuthItem::find()->all(), 'name', 'description'),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите роль -'],
    'hideSearch' => true,
]); ?>

<?= $form->field($user, 'office_id')->widget(Select2::className(), [
    'data' => Offices::arrayMapForSelect2(),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите офис -'],
    'hideSearch' => true,
]) ?>

<?= $form->field($user, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>

<?= $form->field($user, 'password_confirm')->passwordInput(['placeholder' => 'Подтвердите пароль']) ?>
