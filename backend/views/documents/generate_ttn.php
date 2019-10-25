<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\widgets\MaskedInput;
use common\models\Organizations;

/* @var $this yii\web\View */
/* @var $model common\models\DocumentTtnGenerationForm */

$this->title = 'Формирование товарно-транспортной накладной | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Генерация ТТН';

$projectTemplate = '{label}<div class="input-group"><span class="input-group-addon">ID</span>{input}</div>{error}';
?>
<div class="generate-ttn">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'date')->widget(DateControl::className(), [
                'value' => $model->date,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                    'layout' => '<div class="input-group">{input}{picker}</div>',
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'project_id', ['template' => $projectTemplate])->widget(MaskedInput::className(), [
                'clientOptions' => ['alias' =>  'numeric'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => 'Введите ID проекта, по которому требуется сформировать Акт приема-перелачи',
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'org_id')->widget(\kartik\select2\Select2::className(), [
                'data' => Organizations::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'ca_name')->textInput(['placeholder' => 'Введите наименование заказчика']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ca_contact_person')->textInput(['placeholder' => 'Введите ФИО представителя', 'title' => 'Введите ФИО контактного лица, представителя заказчика']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ca_address')->textInput(['placeholder' => 'Введите адрес погрузки']) ?>

        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сформировать', ['class' => 'btn btn-primary btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
