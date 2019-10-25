<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model backend\models\DocumentsImportForm */

$pageTitle = 'Импорт счетов из Fresh Office';
$this->title = $pageTitle . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = $pageTitle;
?>
<div class="import-invoices-form">
    <?php $form = ActiveForm::begin(); ?>

    <p>Форма предназначена для импорта счетов из CRM Fresh Office в качестве документов в данное веб-приложение.</p>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'periodStart')->widget(DateControl::class, [
                'value' => $model->periodStart,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => 'документы с', 'title' => 'Начало периода для отбора по дате оплаты', 'autocomplete' => 'off'],
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
            <?= $form->field($model, 'periodEnd')->widget(DateControl::class, [
                'value' => $model->periodEnd,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => 'дата по', 'title' => 'Конец периода для отбора по дате оплаты', 'autocomplete' => 'off'],
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
    </div>
    <?= Html::submitButton('<i class="fa fa-cog"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>
