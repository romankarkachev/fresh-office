<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use backend\controllers\PoController;
use common\models\AuthItem;
use common\models\PaymentOrdersSearch;

/* @var $this yii\web\View */
/* @var $model common\models\PoSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $roleName string */

if ($roleName == AuthItem::ROLE_ACCOUNTANT)
    $groupStates = PaymentOrdersSearch::fetchGroupStatesForAccountant();
else
    $groupStates = PaymentOrdersSearch::fetchRegularGroupStates();

$formNameId = strtolower($model->formName());
?>

<div class="po-search">
    <?php $form = ActiveForm::begin([
        'action' => PoController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchPaymentDateStart')->widget(DateControl::class, [
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
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) { anyDateOnChange(); }',
                                    ],
                                ],
                            ]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchPaymentDateEnd')->widget(DateControl::class, [
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
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) { anyDateOnChange(); }',
                                    ],
                                ],
                            ]) ?>

                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchAmountStart', [
                                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                            ])->widget(MaskedInput::class, [
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
                            ])->widget(MaskedInput::class, [
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
                <?php if ($roleName != AuthItem::ROLE_ACCOUNTANT_SALARY): ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'created_by')->widget(Select2::class, [
                        'data' => \common\models\User::arrayMapForSelect2(\common\models\User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchDeleted')->widget(Select2::class, [
                        'data' => $model::arrayMapOfDeletedForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <?php endif; ?>
            </div>
            <div class="row">
                <?php if ($roleName != AuthItem::ROLE_ACCOUNTANT_SALARY): ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchAdvancedReports')->widget(Select2::class, [
                        'data' => $model::arrayMapOfAdvancedReportsForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchEiGroup')->widget(Select2::class, [
                        'data' => \common\models\PoEig::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            'select2:select' => new JsExpression('function() { $("#' . $formNameId . '-ei_id").val("").trigger("change"); }'),
                        ],
                    ]) ?>

                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'ei_id')->widget(Select2::class, [
                        'data' => \common\models\PoEi::arrayMapByGroupsForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            'select2:select' => new JsExpression('function() { $("#' . $formNameId . '-searcheigroup").val("").trigger("change"); }'),
                        ],
                    ]) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'searchGroupStates', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($groupStates, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($groupStates) {
                            $hint = '';
                            $key = array_search($value, array_column($groupStates, 'id'));
                            if ($key !== false && isset($groupStates[$key]['hint'])) $hint = ' title="' . $groupStates[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', PoController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
