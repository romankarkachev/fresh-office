<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedByTime */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $states array статусы, которые доступны пользователю в зависимости от раздела учета */
?>
<?= $form->field($model, 'state_id')->widget(Select2::className(), [
    'data' => $states,
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите -'],
]) ?>
