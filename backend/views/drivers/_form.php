<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use common\models\Ferrymen;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="drivers-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?php if ($model->ferryman != null): ?>
        <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

        <?php else: ?>
        <div class="col-md-2 col-lg-2">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <?php endif ?>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'surname')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Фамилия']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Имя']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'patronymic')->textInput(['maxlength' => true, 'placeholder' => 'Отчество']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'driver_license')->textInput(['maxlength' => true, 'placeholder' => 'Номер водит. удост.']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dl_issued_at')->widget(DateControl::className(), [
                'value' => $model->dl_issued_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Выберите дату выдачи',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'pass_serie')->textInput(['placeholder' => 'Введите серию']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pass_num')->textInput(['placeholder' => 'Введите номер']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pass_issued_at')->widget(DateControl::className(), [
                'value' => $model->pass_issued_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Выберите дату выдачи',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'pass_issued_by')->textInput(['placeholder' => 'Введите орган, кем выдан паспорт']) ?>

        </div>
    </div>
    <div class="form-group">
        <?php if ($model->ferryman != null): ?>
        <div class="btn-group">
            <button class="btn btn-default btn-lg dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Вернуться <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><?= Html::a($model->ferryman->name, ['/ferrymen/update', 'id' => $model->ferryman->id], ['title' => 'Вернуться в карточку перевозчика. Изменения не будут сохранены']) ?></li>
                <li><?= Html::a('Водители перевозчика', ['/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman->id]], ['title' => 'Перейти в список водителей перевозчика. Изменения не будут сохранены']) ?></li>
            </ul>
        </div>
        <?php else: ?>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Водители', ['/ferrymen-drivers'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список водителей. Изменения не будут сохранены']) ?>

        <?php endif; ?>
        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
