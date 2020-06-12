<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgets */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $data array отобранные роли/пользователи */
/* @var $label string метка поля */
?>
    <?= $form->field($model, 'entity_id')->widget(Select2::class, [
        'data' => $data,
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- выберите -'],
    ])->label($label) ?>
