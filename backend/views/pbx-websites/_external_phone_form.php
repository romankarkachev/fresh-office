<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\pbxExternalPhoneNumber */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="pbx-external-phone-number-form">
    <div class="panel panel-success">
        <div class="panel-heading">Добавление нового номера</div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'frmNewExternalPhone',
                'action' => \backend\controllers\PbxWebsitesController::URL_ADD_EXTERNAL_PHONE_AS_ARRAY,
                'options' => ['data-pjax' => true],
            ]); ?>

            <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите номер телефона']) ?>

            <?= $form->field($model, 'website_id')->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
