<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataSet array */

function preprareValue($content, $class) {
    return \yii\bootstrap\Html::tag('span', $content, ['class' => 'text-' . $class]);
}

$ferryman = $model->ferryman;
$additionalInfo = [];
if (count($ferryman->drivers) == 0) {
    $additionalInfo[] = preprareValue('нет ни одного водителя', 'danger');
}
else {
    $additionalInfo[] = preprareValue('есть водители', 'primary');
}

if (count($ferryman->transport) == 0) {
    $additionalInfo[] = preprareValue('нет ни одного транспортного средства', 'danger');
}
else {
    $additionalInfo[] = preprareValue('есть транспорт', 'primary');
}

if (empty($ferryman->contract_expires_at)) {
    $additionalInfo[] = preprareValue('срок действия договора не установлен', 'warning');
}
elseif (strtotime($ferryman->contract_expires_at . ' 00:00:00') < strtotime(date('Y-m-d 00:00:00', time()))) {
    $additionalInfo[] = preprareValue('договор просрочен', 'danger text-bold');
}
else {
    $additionalInfo[] = preprareValue('договор действует', 'primary');
}
?>
<div class="row">
    <?php if (!empty($ferryman) && !empty($ferryman->ati_code)): ?>
    <div class="col-md-2">
        <?= $form->field($model, 'pay_till')->widget(DateControl::class, [
            'value' => $model->pay_till,
            'type' => DateControl::FORMAT_DATE,
            'displayFormat' => 'php:d.m.Y',
            'saveFormat' => 'php:Y-m-d',
            'widgetOptions' => [
                'layout' => '{input}{picker}',
                'options' => [
                    'placeholder' => 'Выберите дату',
                    'title' => 'Это крайний срок оплаты по данному перевозчику',
                ],
                'pluginOptions' => [
                    'weekStart' => 1,
                    'autoclose' => true,
                ],
            ],
        ]) ?>

    </div>
    <?php endif; ?>
    <?php if (!empty($dataSet)): ?>
    <div class="col-md-6">
            <?= $form->field($model, 'pd_id')->widget(Select2::class, [
                'data' => $dataSet,
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

    </div>
    <?php endif; ?>
</div>
<?php if (!empty($additionalInfo)): ?>
<div class="form-group">
    <p>
        <i class="fa fa-info-circle text-info" aria-hidden="true"></i>
        <?= implode(', ', $additionalInfo) ?>

    </p>
</div>
<?php endif; ?>
