<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Новый водитель перевозчика ' . $model->ferryman->name . HtmlPurifier::process(' &mdash; Перевозчики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
$this->params['breadcrumbs'][] = 'Новый водитель *';
?>

<div class="drivers-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'surname')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Фамилия']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Имя']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'patronymic')->textInput(['maxlength' => true, 'placeholder' => 'Отчество']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'driver_license')->textInput(['maxlength' => true, 'placeholder' => 'Номер водит. удост.']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . $model->ferryman->name, ['/ferrymen/update', 'id' => $model->ferryman->id], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в карточку перевозчика. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
