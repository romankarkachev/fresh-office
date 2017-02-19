<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'is_deleted') ?>

    <?= $form->field($model, 'author_id') ?>

    <?php // echo $form->field($model, 'type') ?>

    <?php // echo $form->field($model, 'unit') ?>

    <?php // echo $form->field($model, 'uw') ?>

    <?php // echo $form->field($model, 'dc') ?>

    <?php // echo $form->field($model, 'fkko') ?>

    <?php // echo $form->field($model, 'fkko_date') ?>

    <?php // echo $form->field($model, 'fo_id') ?>

    <?php // echo $form->field($model, 'fo_name') ?>

    <?php // echo $form->field($model, 'fo_fkko') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
