<?php

use common\models\EcoMcTp;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\EcoMcTp */
/* @var $parentModel common\models\EcoMc */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $counter integer счетчик добавленных регламентированных отчетов */

$formName = strtolower($model->formName());
?>

<div class="card" id="<?= EcoMcTp::DOM_IDS['ROW_ID'] ?>-<?= $counter ?>">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'report_id')->widget(Select2::class, [
                    'data' => \common\models\EcoReportsKinds::arrayMapForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'id' => $formName . '-report_id-' . $counter,
                        'name' => $parentModel->formName() . '[crudeReports][' . $counter . '][report_id]',
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
                    'saveOptions' => [
                        'name' => $parentModel->formName() . '[crudeReports][' . $counter . '][date_deadline]',
                    ],
                    'options' => ['id' => $formName . '-date_deadline-' . $counter],
                    'widgetOptions' => [
                        'options' => ['placeholder' => '- выберите -', 'autocomplete' => 'off'],
                        'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                        'layout' => '<div class="input-group">{input}{picker}</div>',
                        'pickerButton' => '<span class="input-group-addon kv-date-calendar" title="Выбрать дату"><i class="fa fa-calendar" aria-hidden="true"></i></span>',
                        'pluginOptions' => [
                            'todayHighlight' => true,
                            'weekStart' => 1,
                            'autoclose' => true,
                        ],
                    ],
                ]) ?>

            </div>
        </div>
    </div>
</div>
