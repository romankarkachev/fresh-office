<?php

use backend\controllers\TasksController;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use common\models\TransportRequests;
use common\models\User;
use common\models\TasksSearch;
use common\models\foManagers;

/* @var $this yii\web\View */
/* @var $model common\models\TasksSearch */
/* @var $form yii\bootstrap\ActiveForm */

$tasksSources = TasksSearch::fetchTasksSources();
?>

<div class="tasks-search">
    <?php $form = ActiveForm::begin([
        'action' => TasksController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'searchSource', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($tasksSources, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($tasksSources) {
                            $hint = '';
                            $key = array_search($value, array_column($tasksSources, 'id'));
                            if ($key !== false && isset($tasksSources[$key]['hint'])) $hint = ' title="' . $tasksSources[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'fo_ca_id')->widget(Select2::class, [
                        'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'minimumInputLength' => 1,
                            'language' => 'ru',
                            'ajax' => [
                                'url' => Url::to(['correspondence-packages/counteragent-casting-by-name']),
                                'delay' => 500,
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'responsible_id')->widget(Select2::class, [
                        'data' => $model->searchSource == TasksSearch::TASK_SOURCE_WEB_APP ? User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_ROLE) : foManagers::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPostponedCount')->widget(MaskedInput::class, [
                        'mask' => '99999',
                        'clientOptions' => ['placeholder' => ''],
                    ])->textInput(['maxlength' => true, 'placeholder' => 'Введите число']) ?>

                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCreatedAtStart')->widget(DateControl::class, [
                        'value' => $model->searchCreatedAtStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'начало периода', 'title' => 'Начало периода для отбора по дате создания'],
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
                    <?= $form->field($model, 'searchCreatedAtEnd')->widget(DateControl::class, [
                        'value' => $model->searchCreatedAtEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'конец периода', 'title' => 'Конец периода для отбора по дате создания'],
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
                    <?= $form->field($model, 'searchStartAtStart')->widget(DateControl::class, [
                        'value' => $model->searchStartAtStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'начало периода', 'title' => 'Начало периода для отбора по дате начала события'],
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
                    <?= $form->field($model, 'searchStartAtEnd')->widget(DateControl::class, [
                        'value' => $model->searchStartAtEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'конец периода', 'title' => 'Конец периода для отбора по дате начала события'],
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
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Забытые', ['class' => 'btn btn-info', 'id' => 'btnSearchForgotten', 'title' => 'Выполнить отбор забытых задач', 'name' => 'filter_forgotten']) ?>

        <?= Html::submitButton('На сегодня', ['class' => 'btn btn-info', 'id' => 'btnSearchToday', 'title' => 'Выполнить отбор задач на сегодня', 'name' => 'filter_today']) ?>

        <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

        <?= Html::a('Отключить отбор', TasksController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
