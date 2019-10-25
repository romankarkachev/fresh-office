<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataSet array */
?>
<div class="row">
    <?php if (!empty($model->ferryman) && !empty($model->ferryman->ati_code)): ?>
    <div class="col-md-2">
        <?= $form->field($model, 'pay_till')->widget(DateControl::className(), [
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
            <?= $form->field($model, 'pd_id')->widget(Select2::className(), [
                'data' => $dataSet,
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

    </div>
    <?php endif; ?>
</div>
