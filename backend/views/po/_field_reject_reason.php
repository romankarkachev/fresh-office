<?php

/* @var $this yii\web\View */
/* @var $model common\models\Po */

$formNameId = strtolower($model->formName());
?>

<div class="form-group">
    <?= yii\helpers\Html::textarea($model->formName() . '[reject_reason]', null, [
        'id' => $formNameId . '-reject_reason',
        'class' => 'form-control',
        'placeholder' => 'Введите причину отказа',
        'rows' => 4,
    ]) ?>

</div>
