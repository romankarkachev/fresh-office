<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use \yii\web\JsExpression;
use common\models\pbxWebsites;
use common\models\pbxCalls;

/* @var $this yii\web\View */
/* @var $model common\models\pbxCallsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $predefinedPeriods array доступные предустановленные периоды для отбора по ним */
/* @var $callsDirections array доступные направления звонков для отбора по ним */
/* @var $isNewVariations array доступные варианты для отбора по полю "Новый" */
?>

<div class="pbx-calls-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/pbx-calls'],
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="control-label">Предустановки</label>
                    <?= \yii\bootstrap\ButtonGroup::widget([
                        'buttons' => $predefinedPeriods
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCallPeriodStart')->widget(DateControl::className(), [
                        'value' => $model->searchCallPeriodStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'дата звонка с', 'title' => 'Начало периода для отбора по дате звонка'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) {
anyDateOnChange();
                                }',
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCallPeriodEnd')->widget(DateControl::className(), [
                        'value' => $model->searchCallPeriodEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'дата звонка по', 'title' => 'Конец периода для отбора по дате звонка'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) {
anyDateOnChange();
                                }',
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-3 col-lg-2">
                    <?= $form->field($model, 'website_id')->widget(Select2::className(), [
                        'data' => pbxWebsites::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'searchEntire')->textInput(['placeholder' => 'Введите произвольное значение для поиска']) ?>

                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-lg-3">
                    <?= $form->field($model, 'searchCallDirection', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($callsDirections, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($callsDirections) {
                            $hint = '';
                            $key = array_search($value, array_column($callsDirections, 'id'));
                            if ($key !== false && isset($callsDirections[$key]['hint'])) $hint = ' title="' . $callsDirections[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
                <div class="col-md-3 col-lg-3">
                    <?= $form->field($model, 'searchOnlyNew', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($isNewVariations, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($isNewVariations) {
                            $hint = '';
                            $key = array_search($value, array_column($isNewVariations, 'id'));
                            if ($key !== false && isset($isNewVariations[$key]['hint'])) $hint = ' title="' . $isNewVariations[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
                <div class="col-md-3 col-lg-2">
                    <?= $form->field($model, 'disposition')->widget(Select2::className(), [
                        'data' => pbxCalls::arrayMapOfCallsStatesForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'fo_ca_id')->widget(Select2::className(), [
                        'initValueText' => \common\models\TransportRequests::getCustomerName($model->fo_ca_id),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'language' => 'ru',
                            'ajax' => [
                                'url' => \yii\helpers\Url::to(['projects/direct-sql-counteragents-list']),
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
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-filter" aria-hidden="true"></i> Выполнить отбор', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('<i class="fa fa-times" aria-hidden="true"></i> Отключить отбор', ['/pbx-calls'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
