<?php

use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\AuthItem;
use common\models\Offices;

/* @var yii\widgets\ActiveForm $form */
/* @var \common\models\User $user */
?>

<?= $form->field($user, 'username')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'email')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>
