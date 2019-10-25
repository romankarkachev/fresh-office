<?php

use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use common\models\TransportRequests;
use common\models\StorageTtnRequired;

/* @var $this yii\web\View */
/* @var $model \common\models\StorageTtnRequired */
?>
<?php switch ($model->type): ?>
<?php case StorageTtnRequired::TYPE_КОНТРАГЕНТ: ?>
<?= $form->field($model, 'entity_id')->widget(Select2::className(), [
    'initValueText' => TransportRequests::getCustomerName($model->entity_id),
    'theme' => Select2::THEME_BOOTSTRAP,
    'language' => 'ru',
    'options' => ['placeholder' => 'Введите наименование'],
    'pluginOptions' => [
        'allowClear' => true,
        'minimumInputLength' => 1,
        'language' => 'ru',
        'ajax' => [
            'url' => Url::to(['projects/direct-sql-counteragents-list']),
            'delay' => 500,
            'dataType' => 'json',
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(result) { return result.text; }'),
        'templateSelection' => new JsExpression('function (result) { return result.text; }'),
    ],
]) ?>
<?php break; ?>
<?php case StorageTtnRequired::TYPE_ОТВЕТСТВЕННЫЙ: ?>
<?= $form->field($model, 'entity_id')->widget(Select2::className(), [
    'data' => \common\models\foManagers::arrayMapForSelect2(),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите -'],
]) ?>
<?php break; ?>
<?php case StorageTtnRequired::TYPE_ПРОЕКТ: ?>
<?= $form->field($model, 'entity_id')->widget(MaskedInput::className(), [
    'mask' => '99999',
    'clientOptions' => ['placeholder' => ''],
])->textInput(['maxlength' => true, 'placeholder' => 'ID проекта']) ?>
<?php break; ?>
<?php endswitch; ?>
