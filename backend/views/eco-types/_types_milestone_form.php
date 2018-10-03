<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\EcoTypesMilestones */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="eco-type-milestone-form">
    <div class="panel panel-success">
        <div class="panel-heading">Добавление нового этапа</div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'frmNewMilestone',
                'action' => \backend\controllers\EcoTypesController::URL_ADD_MILESTONE_AS_ARRAY,
                'options' => ['data-pjax' => true],
            ]); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'milestone_id')->widget(Select2::className(), [
                        'data' => \common\models\EcoMilestones::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'time_to_complete_required', ['template' => '{label}<div class="input-group">{input}<span class="input-group-addon">дней</span></div>{error}'])->widget(MaskedInput::className(), [
                        'clientOptions' => ['alias' =>  'numeric'],
                    ])->textInput([
                        'maxlength' => true,
                        'placeholder' => '0',
                        'title' => 'Введите отведенное количество дней на выполнение конкретно этого этапа',
                    ])->label('Время выполнения') ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'order_no')->widget(MaskedInput::className(), [
                        'clientOptions' => ['alias' =>  'numeric'],
                    ])->textInput([
                        'maxlength' => true,
                        'placeholder' => '0',
                        'title' => 'Введите номер, под которым этот этап должен числиться в списке по данному типу (очередность)',
                    ]) ?>

                </div>
                <div class="col-md-1">
                    <label for="<?= strtolower($model->formName()) ?>-is_file_reqiured" class="control-label" title="<?= $model->getAttributeLabel('is_file_reqiured') ?>">Файл</label>
                    <?= $form->field($model, 'is_file_reqiured')->checkbox()->label(false) ?>

                </div>
                <div class="col-md-1">
                    <label for="<?= strtolower($model->formName()) ?>-is_file_reqiured" class="control-label" title="<?= $model->getAttributeLabel('is_affects_to_cycle_time') ?>">Учитывать в сроках</label>
                    <?= $form->field($model, 'is_affects_to_cycle_time')->checkbox()->label(false) ?>

                </div>
            </div>
            <?= $form->field($model, 'type_id')->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, \yii\web\View::POS_READY);
?>
