<?php

use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use yii\helpers\Html;
use kartik\select2\Select2;
use \backend\controllers\WasteEquipmentController;

/* @var $this yii\web\View */
/* @var $model common\models\WasteEquipment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="waste-equipment-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименовние', 'autofocus' => true]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'year')->widget(MaskedInput::class, [
                'mask' => '9999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '1999',
                'title' => 'Введите год выпуска транспортного средства',
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amort_percent', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon">%</span></div>{error}',
            ])->widget(MaskedInput::class, [
                'clientOptions' => ['alias' =>  'numeric'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => 'Процент износ',
            ])->label('Амортизация') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ownership')->widget(Select2::class, [
                'data' => $model->arrayMapOfOwnershipForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'description')->textarea([
        'rows' => 6,
        'placeholder' => 'Производитель, страна производства, марка, модель, основные технические характеристики',
    ]) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . WasteEquipmentController::ROOT_LABEL, WasteEquipmentController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= $model->renderSubmitButtons() ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
