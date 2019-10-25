<?php

use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model backend\models\FerrymanReplacingForm */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="replace-ferryman-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmReplaceFerryman',
        'action' => '/ferrymen-drivers/replace-ferryman',
        'enableAjaxValidation' => true,
        'validationUrl' => ['/ferrymen-drivers/validate-ferryman-replacing'],
    ]); ?>

    <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
        'data' => \common\models\Ferrymen::arrayMapForSelect2($model->getDriver()->ferryman_id),
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- выберите -'],
    ]) ?>

    <?= $form->field($model, 'driver_id')->hiddenInput()->label(false) ?>

    <?= \yii\helpers\Html::button('Выполнить перевод', ['class' => 'btn btn-default', 'id' => 'btnReplaceFerryman']) ?>

    <?php ActiveForm::end(); ?>

</div>
