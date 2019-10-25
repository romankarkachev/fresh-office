<?php

use common\models\PoEi;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\Products */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'is_deleted')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'type')->textInput() ?>

    <?= $form->field($model, 'src_unit')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'src_uw')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'src_dc')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unit_id')->widget(Select2::class, [
        'data' => \common\models\Units::arrayMapForSelect2(),
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- выберите -'],
    ]) ?>

    <?= $form->field($model, 'hk_id')->widget(Select2::class, [
        'data' => \common\models\HandlingKinds::arrayMapForSelect2(),
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- выберите -'],
    ]) ?>

    <?= $form->field($model, 'dc_id')->widget(Select2::class, [
        'data' => \common\models\DangerClasses::arrayMapForSelect2(),
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- выберите -'],
    ]) ?>

    <?= $form->field($model, 'fkko')->textInput() ?>

    <?= $form->field($model, 'fkko_date')->textInput() ?>

    <?= $form->field($model, 'fo_id')->textInput() ?>

    <?= $form->field($model, 'fo_name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fo_fkko')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
