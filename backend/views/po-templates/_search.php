<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PoAtSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="po-at-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'is_active') ?>

    <?= $form->field($model, 'company_id') ?>

    <?= $form->field($model, 'ei_id') ?>

    <?= $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'comment') ?>

    <?php // echo $form->field($model, 'properties') ?>

    <?php // echo $form->field($model, 'periodicity') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
