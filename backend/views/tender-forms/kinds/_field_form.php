<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsKindsFields */
/* @var $form yii\bootstrap\ActiveForm */
?>
<?php $form = ActiveForm::begin([
    'id' => 'frmNewKind',
    'enableAjaxValidation' => true,
    'validationUrl' => TenderFormsController::URL_ADD_VALIDATE_KF_AS_ARRAY,
    'action' => TenderFormsController::URL_ADD_KIND_FIELD_AS_ARRAY,
    'options' => ['data-pjax' => true],
]); ?>

<div class="panel panel-success">
    <div class="panel-heading">Добавление нового поля</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <?= $form->field($model, 'alias')->textInput(['maxlength' => true, 'placeholder' => 'Введите псевдоним', 'title' => 'Введенное значение должно быть уникальным для этой формы']) ?>

            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование']) ?>

            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'description')->textInput(['maxlength' => true, 'placeholder' => 'Введите предназначение поля']) ?>

            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'widget')->widget(Select2::class, [
                    'data' => $model::arrayMapOfWidgetsForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => ['placeholder' => '- выберите -', 'title' => $model->getAttributeLabel('widget')],
                    'hideSearch' => true,
                ])->label('Виджет') ?>

            </div>
        </div>
        <div class="form-group">
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

        </div>
    </div>
</div>

<?= $form->field($model, 'kind_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?php ActiveForm::end(); ?>
