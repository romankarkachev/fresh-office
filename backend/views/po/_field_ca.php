<?php

/**
 * При выборе статьи расходов "Административные" - "Благодарности" выводится дополнительное поле - "Контрагент из Fresh Office".
 */

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\web\JsExpression;
use common\models\TransportRequests;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?= $form->field($model, 'fo_ca_id')->widget(Select2::class, [
    'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
    'theme' => Select2::THEME_BOOTSTRAP,
    'language' => 'ru',
    'options' => ['placeholder' => 'Введите наименование'],
    'pluginOptions' => [
        'allowClear' => true,
        'minimumInputLength' => 1,
        'language' => 'ru',
        'ajax' => [
            'url' => Url::to(['correspondence-packages/counteragent-casting-by-name']),
            'delay' => 500,
            'dataType' => 'json',
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(result) { return result.text; }'),
        'templateSelection' => new JsExpression('function (result) {
if (!result.custom) return result.text;
$("#' . Html::getInputId($model, 'customer_name') . '").val(result.text);
$("#' . Html::getInputId($model, 'created_by') . '").val(result.managerId).trigger("change");

return result.text;
}'),
    ],
]) ?>
