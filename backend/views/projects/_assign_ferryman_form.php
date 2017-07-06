<?php

use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\Ferrymen;

/* @var $this yii\web\View */
/* @var $model common\models\AssignFerrymanForm */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="assign-ferryman-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmAssignFerryman',
        'action' => '/projects/assign-ferryman',
        'enableAjaxValidation' => true,
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'project_ids')->widget(Select2::className(), [
                'options' => ['multiple' => true],
                'pluginOptions' => [
                    'tags' => true,
                    'tokenSeparators' => [',', ' '],
                    'maximumInputLength' => 10
                ],
                'readonly' => true,
            ]) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginEvents' => [
                    'change' => new JsExpression('function() { ferrymanOnChange(); }'),
                ],
            ]) ?>

        </div>
    </div>
    <div class="row" id="block-fields">
        <?= $form->field($model, 'driver_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'transport_id')->hiddenInput()->label(false) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
