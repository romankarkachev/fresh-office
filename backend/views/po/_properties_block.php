<?php

use common\models\Po;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $properties array свойства и значения свойств */

$prevPropertyId = -1;
$prevPropertyName = '';
$currentProperty = -1;
$currentValue = -1;
$linkId = -1;
$propertyValues = [];
?>
<?php foreach ($properties as $value): ?>
<?php if ($currentProperty != $value['property_id']): ?>
<?php if ($currentProperty != -1): ?>
<?= $this->render('_field_property', [
    'propertyId' => $prevPropertyId,
    'propertyName' => $prevPropertyName,
    'propertyValues' => $propertyValues,
    'currentValue' => $currentValue,
    'linkId' => $linkId,
]); ?>

<?php
$propertyValues = [];
$currentValue = -1;
$linkId = -1;
?>
<?php endif; ?>
<?php endif; ?>
<?php
$propertyValues = ArrayHelper::merge($propertyValues, [
    $value['id'] => $value['name'],
]);
$currentProperty = $value['property_id'];
$prevPropertyId = $value['property_id'];
$prevPropertyName = $value['propertyName'];
if ($value['selected']) {
    $currentValue = $value['id'];
    $linkId = $value['link_id'];
}
?>
<?php endforeach; ?>
<?php if ($currentProperty != -1): ?>
<?= $this->render('_field_property', [
    'propertyId' => $prevPropertyId,
    'propertyName' => $prevPropertyName,
    'propertyValues' => $propertyValues,
    'currentValue' => $currentValue,
    'linkId' => $linkId,
]); ?>
<?php else: ?>
<?= Po::PROMPT_EMPTY_PROPERTIES ?>
<?php endif; ?>
