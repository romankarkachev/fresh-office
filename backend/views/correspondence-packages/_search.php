<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackagesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="correspondence-packages-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'ready_at') ?>

    <?= $form->field($model, 'sent_at') ?>

    <?= $form->field($model, 'fo_project_id') ?>

    <?php // echo $form->field($model, 'customer_name') ?>

    <?php // echo $form->field($model, 'state_id') ?>

    <?php // echo $form->field($model, 'type_id') ?>

    <?php // echo $form->field($model, 'pad') ?>

    <?php // echo $form->field($model, 'pd_id') ?>

    <?php // echo $form->field($model, 'track_num') ?>

    <?php // echo $form->field($model, 'other') ?>

    <?php // echo $form->field($model, 'comment') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
