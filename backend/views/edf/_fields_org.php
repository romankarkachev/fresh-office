<?php

use kartik\select2\Select2;
use common\models\EdfStates;

/* @var $this yii\web\View */
/* @var $model common\models\Edf */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $hasAccess bool наличие доступа к нескольким объектам электронного документа (менеджер не имеет) */

// по просьбе заказчика поле "Номер договора" всегда доступно
//, 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА)
//, 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА)
?>
        <div class="col-md-2">
            <?php if (Yii::$app->user->can('sales_department_manager')): ?>
            <?= $form->field($model, 'bankAccountNumber')->staticControl() ?>

            <?= $form->field($model, 'ba_id')->hiddenInput()->label(false) ?>
            <?php else: ?>
            <?= $form->field($model, 'ba_id')->widget(Select2::className(), [
                'data' => $model->organization->arrayMapOfBankAccountsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
                'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА),
            ]) ?>
            <?php endif; ?>

        </div>
        <div class="col-md-2">
            <?php if (Yii::$app->user->can('sales_department_manager')): ?>
            <?php if ($model->type_id == \common\models\DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ): ?>
            <?= $form->field($model, 'doc_num')->textInput(['maxlength' => true, 'placeholder' => 'Введите номер документа']) ?>
            <?php else: ?>
            <?= $form->field($model, 'doc_num')->staticControl() ?>

            <?= $form->field($model, 'doc_num')->hiddenInput()->label(false) ?>
            <?php endif; ?>
            <?php else: ?>
            <?= $form->field($model, 'doc_num')->textInput(['maxlength' => true, 'placeholder' => 'Введите номер документа']) ?>
            <?php endif; ?>

        </div>
