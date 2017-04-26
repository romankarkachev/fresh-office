<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model backend\models\FreightsPaymentsImport */

$this->title = 'Обновление даты оплаты рейса проектов | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Обновление даты оплаты рейса проектов';
?>
<div class="freightspayments-import">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Примечание</h3>
        </div>
        <div class="box-body">
            <p>Внимание! В файле импорта первая строка должна содержать заголовок.</p>
            <p>Файл импорта должен содержать следующие поля: <strong>ID проекта *</strong> (колонка A), <strong>Сумма *</strong> (колонка G, представляет собой поле Себестоимость).</p>
            <p><strong>Обратите также внимание</strong>, что файл импорта, который Вы предоставляете, должен содержать только один лист в книге. В противном случае импорт не может быть выполнен.</p>
            <p>
                <?= Html::img('/images/freights-payments-import-example.jpg', ['width' => '80%']) ?>

            </p>
        </div>
    </div>
    <?php $form = ActiveForm::begin() ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'importFile')->fileInput() ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'date_payment')->widget(DateControl::className(), [
                'value' => $model->date_payment,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => 'выберите'],
                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                    'layout' => '{input}{picker}{remove}',
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end() ?>

</div>