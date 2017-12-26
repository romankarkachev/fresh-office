<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use kartik\money\MaskMoney;
use common\models\DirectMSSQLQueries;

/* @var $this yii\web\View */
/* @var $model common\models\TransportTypes */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="projects-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                'data' => DirectMSSQLQueries::arrayMapOfProjectsTypesForSelect2(DirectMSSQLQueries::PROJECTS_TYPES_LOGIST_LIMIT),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => DirectMSSQLQueries::arrayMapOfProjectsStatesForSelect2(true),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                'data' => DirectMSSQLQueries::arrayMapOfManagersForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                'initValueText' => $model->ca_name,
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(['projects/direct-sql-counteragents-list']),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                ],
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'amount', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub" aria-hidden="true"></i></span></div>{error}'
            ])->widget(MaskMoney::className()) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'cost', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub" aria-hidden="true"></i></span></div>{error}'
            ])->widget(MaskMoney::className()) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'vivozdate')->widget(DateControl::className(), [
                'value' => $model->vivozdate,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'layout' => '{input}{picker}',
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'oplata')->widget(DateControl::className(), [
                'value' => $model->oplata,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'layout' => '{input}{picker}',
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'date_start')->widget(DateControl::className(), [
                'value' => $model->date_start,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'layout' => '{input}{picker}',
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'date_end')->widget(DateControl::className(), [
                'value' => $model->date_end,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'layout' => '{input}{picker}',
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'perevoz')->textInput(['placeholder' => 'Введите перевозчика']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'proizodstvo')->textInput(['placeholder' => 'Введите произв. площадь']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'adres')->textInput(['placeholder' => 'Введите адрес']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dannie')->textInput(['placeholder' => 'Введите данные']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ttn')->textInput(['placeholder' => 'Введите ТТН']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'weight')->textInput(['placeholder' => 'Введите вес']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Проекты', ['/projects'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
