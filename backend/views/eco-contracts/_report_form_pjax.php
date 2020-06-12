<?php

use backend\controllers\EcoContractsController;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\EcoMcTp */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
?>

<div class="eco-contract-report-form">
    <p class="text-muted text-justify">Обратите внимание, что данная форма &mdash; интерактивная, то есть изменения сохранять не нужно, все действия применяются на лету (в отличие от формы слева).</p>
    <?php $form = ActiveForm::begin([
        'id' => \common\models\EcoMcTp::DOM_IDS['PJAX_FORM_ID'],
        'action' => EcoContractsController::URL_CREATE_REPORT_AS_ARRAY,
        'options' => ['data-pjax' => true],
    ]); ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'report_id')->widget(Select2::class, [
                        'data' => \common\models\EcoReportsKinds::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => [
                            'placeholder' => '- выберите -',
                            'title' => $model->getAttributeLabel('report_id'),
                        ],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'date_deadline')->widget(DateControl::class, [
                        'value' => $model->date_deadline,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => '- выберите -', 'autocomplete' => 'off'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '{input}{picker}',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <label class="control-label">&nbsp;</label>
                    <?= Html::submitButton('Добавить <i class="fa fa-arrow-down"></i> ', ['class' => 'btn btn-success btn-block']) ?>

                </div>
            </div>
        </div>
    </div>
    <?= $form->field($model, 'mc_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
