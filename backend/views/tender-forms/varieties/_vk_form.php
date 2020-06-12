<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\TenderFormsKinds;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsVarietiesKinds */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tender-forms-variety-kind">
    <div class="panel panel-success">
        <div class="panel-heading">Добавление новой формы</div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'frmNewKind',
                'enableAjaxValidation' => true,
                'validationUrl' => TenderFormsController::URL_ADD_VALIDATE_VK_AS_ARRAY,
                'action' => TenderFormsController::URL_ADD_VARIETY_KIND_AS_ARRAY,
                'options' => ['data-pjax' => true],
            ]); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'kind_id')->widget(Select2::class, [
                        'data' => TenderFormsKinds::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
            <?= $form->field($model, 'variety_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
