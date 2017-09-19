<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankCards */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="ferrymen-bank-cards-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'cardholder')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя держателя карты']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'number')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '9999999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['placeholder' => 'Введите номер банковской карты']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'bank')->textInput([
                'maxlength' => true,
                'placeholder' => 'Введите наименование банка',
                'title' => 'Введите наименование банка, в котором открыта карта',
            ]) ?>

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
