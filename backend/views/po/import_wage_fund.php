<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\widgets\MaskedInput;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \yii\base\DynamicModel */
/* @var array $propertiesFields значения свойств статьи */
/* @var array $amProperties массив допустимых свойств статей расходов */

$this->title = 'Импорт в бюджет платежных ордеров по отчислениям на ФОТ | '.Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PoController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Импорт налогов на ФОТ';
?>
<div class="import-wage-fund">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Примечание</h3>
        </div>
        <div class="box-body">
            <p>Файл импорта должен содержать
                <strong>ФИО сотрудника *</strong> (колонка A),
                остальные поля Вы указываете самостоятельно. Добавьте в поле &laquo;Комментарий&raquo; переменную <strong>%TAX_NAME%</strong>, чтобы дополнить текст названием отчисления.
            </p>
            <p><strong>Обратите также внимание</strong>, что файл импорта, который Вы предоставляете, должен содержать только один лист в книге. В противном случае импорт не может быть выполнен.</p>
        </div>
    </div>
    <?php $form = ActiveForm::begin() ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'ei_id')->widget(Select2::class, [
                'data' => \common\models\PoEi::arrayMapForSelect2(['group_id' => \common\models\PoEig::ГРУППА_НАЛОГИ]),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ])->label('Статья расходов') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'paid_at')->widget(DateControl::className(), [
                'value' => $model->paid_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Выберите дату оплаты',
                        'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ])->label('Дата оплаты') ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'comment')->textInput(['placeholder' => 'Например, ФОТ январь', 'title' => 'Произвольный комментарий, будет включен первой строкой в каждый платежный ордер'])->label('Комментарий') ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'importFile')->fileInput()->label('Файл') ?>

        </div>
    </div>
    <div class="row">
        <?php foreach ($propertiesFields as $name => $label): ?>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-8">
                    <?= $form->field($model, $name . '_id')->widget(Select2::class, [
                        'data' => $amProperties,
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ])->label('Свойство ' . $label) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, $name . '_col')->widget(MaskedInput::className(), [
                        'mask' => 'A',
                        'clientOptions' => ['placeholder' => ''],
                    ])->textInput([
                        'maxlength' => 1,
                        'placeholder' => 'Буква',
                        'title' => 'Введите символ колонки, из которой будет извлечено значение ' . $label,
                    ])->label('Колонка') ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end() ?>

</div>
