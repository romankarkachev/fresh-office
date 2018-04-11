<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\FerrymanOrderForm;
use common\models\Organizations;
use common\models\Ferrymen;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymanOrderForm */
/* @var $form yii\bootstrap\ActiveForm */

$vatKinds = FerrymanOrderForm::fetchVatKinds();
?>

<div class="assign-ferryman-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmAssignFerryman',
        'action' => '/projects/export-ferryman-order',
        'enableAjaxValidation' => true,
        'validationUrl' => ['/projects/validate-ferryman-order'],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                'data' => Organizations::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите организацию -'],
            ]) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите перевозчика -'],
                'pluginEvents' => [
                    'change' => new JsExpression('function() { ferrymanOnChange(2, $(this).val()); }'),
                ],
            ]) ?>

        </div>
    </div>
    <div class="row" id="block-fields">
        <?php if ($model->ferryman_id == null): ?>
        <?= $form->field($model, 'driver_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'transport_id')->hiddenInput()->label(false) ?>
        <?php else: ?>
        <?= $this->renderAjax('_assign_ferryman_fields', ['model' => $model, 'form' => $form]) ?>
        <?php endif; ?>

    </div>
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'unload_address')->textInput(['placeholder' => 'Адрес выгрузки']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'load_time')->textInput(['placeholder' => 'Дата выгрузки']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'amount', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::className(), [
                'clientOptions' => ['alias' =>  'numeric'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'hasVat', [
                'inline' => true,
            ])->radioList(ArrayHelper::map($vatKinds, 'id', 'name'), [
                'class' => 'btn-group',
                'data-toggle' => 'buttons',
                'unselect' => null,
                'item' => function ($index, $label, $name, $checked, $value) use ($vatKinds) {
                    $hint = '';
                    $key = array_search($value, array_column($vatKinds, 'id'));
                    if ($key !== false && isset($groups[$key]['hint'])) $hint = ' title="' . $vatKinds[$key]['hint'] . '"';

                    return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                        Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                },
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'special_conditions')->textarea(['rows' => 3, 'placeholder' => 'Особые условия']) ?>

    <?= $form->field($model, 'project_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
