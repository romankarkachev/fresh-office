<?php

use kartik\select2\Select2;
use common\models\TendersLossReasons;

/* @var $this yii\web\View */
/* @var $model common\models\Tenders */

$formNameId = strtolower($model->formName());
?>

<div class="row">
    <div class="col-md-4">
        <label class="control-label" for="<?= $formNameId . '-loss_reason' ?>">Выберите причину проигрыша</label>
        <?= Select2::widget([
            'id' => $formNameId . '-loss_reason',
            'name' => $model->formName() . '[loss_reason]',
            'data' => TendersLossReasons::arrayMapForSelect2(),
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => ['placeholder' => '- выберите -'],
            'hideSearch' => true,
        ]) ?>

    </div>
</div>
<?= yii\bootstrap\Html::hiddenInput($model->formName() . '[mode]', 1, ['id' => common\models\Tenders::DOM_IDS['REASON_MODE_ID']]) ?>
