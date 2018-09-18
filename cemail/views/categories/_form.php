<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CEMailboxesCategories */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="cemailboxes-categories-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'inputOptions' => [
                'class' => 'form-control',
            ],
            'errorOptions' => ['class' => 'text-danger'],
        ],
    ]); ?>

    <div class="card">
        <div class="card-block">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="card-footer text-muted">
            <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Категории', ['/categories'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

            <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
            <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
            <?php endif; ?>

        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
