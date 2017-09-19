<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model common\models\CounteragentsPostAddresses */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="counteragents-post-addresses-form">
    <?php Pjax::begin(['id' => 'pjax-form']); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'frmNewPostAddress',
        'options' => ['data-pjax' => true],
    ]); ?>

    <?= $form->field($model, 'counteragent_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'src_address')->textInput(['placeholder' => 'Введите адрес'])->label('Почтовый адрес') ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'zip_code')->textInput(['maxlength' => true, 'placeholder' => 'Индекс']) ?>

        </div>
        <div class="col-md-10">
            <?= $form->field($model, 'address_m')->textInput(['placeholder' => 'Нормализованный адрес']) ?>

        </div>
    </div>
    <div class="form-group">
        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

    <?php Pjax::end(); ?>

</div>
