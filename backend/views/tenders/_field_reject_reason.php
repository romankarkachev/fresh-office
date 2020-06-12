<?php

/* @var $this yii\web\View */
/* @var $model common\models\Tenders */

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
<?= yii\bootstrap\Html::hiddenInput($model->formName() . '[mode]', 2, ['id' => common\models\Tenders::DOM_IDS['REASON_MODE_ID']]) ?>
