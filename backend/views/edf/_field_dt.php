<?php

use kartik\select2\Select2;
use common\models\EdfStates;

/* @var $this yii\web\View */
/* @var $model common\models\Edf */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $hasAccess bool наличие доступа к нескольким объектам электронного документа (менеджер не имеет) */
?>
<?php if ($model->type_id == \common\models\DocumentsTypes::TYPE_ДОГОВОР): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'ct_id')->widget(Select2::className(), [
                'data' => \common\models\ContractTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
                //'disabled' => ($hasAccess && !$model->isNewRecord && !in_array($model->state_id, [EdfStates::STATE_ЧЕРНОВИК, EdfStates::STATE_ОТКАЗ])) || ($model->state_id > EdfStates::STATE_ЗАЯВКА && $model->state_id != EdfStates::STATE_ОТКАЗ),
                'disabled' => $model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && $model->state_id != EdfStates::STATE_ОТКАЗ_КЛИЕНТА && $model->state_id != EdfStates::STATE_УТВЕРЖДЕНО,
            ]) ?>

        </div>
<?php elseif ($model->type_id == \common\models\DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'parent_id')->widget(Select2::className(), [
                'data' => \common\models\Edf::arrayMapOfContractsForSelect2($model->fo_ca_id),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
                'disabled' => (Yii::$app->user->can('root') || Yii::$app->user->can('operator_head') ? false : ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id > EdfStates::STATE_ЗАЯВКА && $model->state_id != EdfStates::STATE_ОТКАЗ)),
                'pluginEvents' => [
                    'select2:select' => new \yii\web\JsExpression('function() {
    parentOnChange();
}'),
                ],
            ]) ?>

        </div>
<?php endif; ?>
