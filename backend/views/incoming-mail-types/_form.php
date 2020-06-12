<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\IncomingMailTypesController;

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMailTypes */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="incoming-mail-types-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование', 'autofocus' => true]) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . IncomingMailTypesController::MAIN_MENU_LABEL, IncomingMailTypesController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
