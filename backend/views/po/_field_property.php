<?php

use kartik\select2\Select2;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $propertyId integer идентификатор свойства */
/* @var $propertyName string наименование свойства */
/* @var $propertyValues array массив со значениями текущего свойства */
/* @var $currentValue bool признак, определяющий необходимость установить конкретное значение, а также позволяющий удалить связку */
/* @var $linkId integer идентификатор связки, если она есть */
?>
<div class="row"<?= !empty($linkId) ? ' id="link-row-' . $linkId . '"' : '' ?>>
    <div class="col-md-3">
        <label class="control-label small"><?= $propertyName ?></label>
        <?= Select2::widget([
            'id' => 'po-propertiesValues-' . $propertyId,
            'name' => 'Po[propertiesValues][' . $propertyId . '][value_id]',
            'data' => $propertyValues,
            'theme' => Select2::THEME_BOOTSTRAP,
            'size' => Select2::SMALL,
            'options' => ['placeholder' => '- выберите -'],
            'value' => $currentValue,
        ]) ?>

        <?php if (count($propertyValues) == 1 && !empty($propertyValues[0]['selected'])): ?>
        <?php endif; ?>
    </div>
    <?php if (!empty($linkId) && $linkId != -1): ?>
    <div class="col-md-1">
        <label class="control-label">&nbsp;</label>
        <div class="form-group">
            <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', [
                'id' => 'btnDeleteValueLink-' . $linkId,
                'class' => 'btn btn-danger btn-xs',
                'title' => 'Удалить это значение',
                'data-id' => $linkId,
            ]) ?>

        </div>
    </div>
    <?php endif; ?>
</div>
