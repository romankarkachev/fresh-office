<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\EcoTypesController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoTypes */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="eco-types-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . EcoTypesController::ROOT_LABEL, EcoTypesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

    <?php if ($model->isNewRecord): ?>
    <p>Добавить этапы в тип проекта можно только после его создания.</p>
    <?php endif; ?>
</div>
