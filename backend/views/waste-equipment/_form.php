<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \backend\controllers\WasteEquipmentController;

/* @var $this yii\web\View */
/* @var $model common\models\WasteEquipment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="waste-equipment-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименовние', 'autofocus' => true]) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . WasteEquipmentController::ROOT_LABEL, WasteEquipmentController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= $model->renderSubmitButtons() ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
