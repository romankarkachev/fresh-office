<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\DesktopWidgetsController;

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgets */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="desktop-widgets-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

    <?= $form->field($model, 'alias')->textInput(['maxlength' => true, 'placeholder' => 'Введите псевдоним']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Введите описание']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . DesktopWidgetsController::ROOT_LABEL, DesktopWidgetsController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
