<?php

use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use backend\controllers\AdvanceReportsController;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\PoSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $renderRestricted bool признак, определяющий расширенное или ограниченное использование инструмента */
?>

<div class="po-search">
    <?php $form = ActiveForm::begin([
        'action' => AdvanceReportsController::URL_ROOT_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchPaymentDateStart')->widget(DateControl::className(), [
                                'value' => $model->searchPaymentDateStart,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => ['placeholder' => 'дата оплаты с', 'title' => 'Начало периода для отбора по дате оплаты', 'autocomplete' => 'off'],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '{input}{picker}{remove}',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => ['changeDate' => 'function(e) { anyDateOnChange(); }'],
                                ],
                            ]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchPaymentDateEnd')->widget(DateControl::className(), [
                                'value' => $model->searchPaymentDateEnd,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => ['placeholder' => 'дата оплаты по', 'title' => 'Конец периода для отбора по дате оплаты', 'autocomplete' => 'off'],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '{input}{picker}{remove}',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => ['changeDate' => 'function(e) { anyDateOnChange(); }'],
                                ],
                            ]) ?>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-xl-2">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'searchAmountStart', [
                                    'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                                ])->widget(MaskedInput::className(), [
                                    'clientOptions' => [
                                        'alias' =>  'numeric',
                                        'groupSeparator' => ' ',
                                        'autoUnmask' => true,
                                        'autoGroup' => true,
                                        'removeMaskOnSubmit' => true,
                                    ],
                                ])->textInput([
                                    'maxlength' => true,
                                    'placeholder' => '0',
                                    'title' => 'При заполнении только этого поля условие интерпретируется как "Сумма включительно и более"',
                                ]) ?>

                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'searchAmountEnd', [
                                    'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                                ])->widget(MaskedInput::className(), [
                                    'clientOptions' => [
                                        'alias' =>  'numeric',
                                        'groupSeparator' => ' ',
                                        'autoUnmask' => true,
                                        'autoGroup' => true,
                                        'removeMaskOnSubmit' => true,
                                    ],
                                ])->textInput([
                                    'maxlength' => true,
                                    'placeholder' => '0',
                                    'title' => 'При заполнении только этого поля условие интерпретируется как "Сумма включительно и менее"',
                                ]) ?>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'company_id')->widget(Select2::class, [
                            'initValueText' => $model->companyName,
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'language' => 'ru',
                            'options' => ['placeholder' => 'Введите наименование (ИНН, ОГРН)'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 1,
                                'language' => 'ru',
                                'ajax' => [
                                    'url' => Url::to(\backend\controllers\CompaniesController::URL_CASTING_AS_ARRAY),
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
                <?php if (!$renderRestricted): ?>
                    <div class="col-md-2">
                        <?= $form->field($model, 'created_by')->widget(Select2::className(), [
                            'data' => \common\models\User::arrayMapForSelect2(\common\models\User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'options' => ['placeholder' => '- выберите -'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>

                    </div>
                <?php endif; ?>
                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', AdvanceReportsController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
